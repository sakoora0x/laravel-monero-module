<?php

namespace sakoora0x\LaravelMoneroModule\Services\Sync;

use Brick\Math\BigDecimal;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use sakoora0x\LaravelMoneroModule\Api\Api;
use sakoora0x\LaravelMoneroModule\Facades\Monero;
use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;
use sakoora0x\LaravelMoneroModule\Models\MoneroDeposit;
use sakoora0x\LaravelMoneroModule\Models\MoneroNode;
use sakoora0x\LaravelMoneroModule\Models\MoneroWallet;
use sakoora0x\LaravelMoneroModule\Services\BaseConsole;
use sakoora0x\LaravelMoneroModule\WebhookHandlers\WebhookHandlerInterface;

class WalletSync extends BaseConsole
{
    protected MoneroWallet $wallet;
    protected ?MoneroNode $node;
    protected ?Api $api;
    protected ?WebhookHandlerInterface $webhookHandler;
    /** @var MoneroDeposit[] */
    protected array $webhooks = [];

    public function __construct(MoneroWallet $wallet, ?MoneroNode $node = null, ?Api $api = null)
    {
        $this->wallet = $wallet;
        $this->node = $node ?? $wallet->node;
        $this->api = $api;

        $model = Monero::getModelWebhook();
        $this->webhookHandler = $model ? App::make($model) : null;
    }

    public function run(): void
    {
        parent::run();

        $this->log("Starting wallet synchronization for {$this->wallet->name}...");

        try {
            Monero::walletAtomicLock($this->wallet, function () {
                $this
                    ->apiConnect()
                    ->openWallet()
                    ->getBalances()
                    ->incomingTransfers()
                    ->runWebhooks();
            }, 5);
        } catch (LockTimeoutException $e) {
            $this->log("Error: wallet is currently locked by another process.", "error");
            return;
        } catch (\Exception $e) {
            $this->log("Error: {$e->getMessage()}", "error");
            return;
        }

        $this->log("Wallet {$this->wallet->name} synchronized successfully!");
    }

    protected function apiConnect(): static
    {
        if (!$this->api) {
            $this->log("Connecting to Node via API...");
            $this->api = $this->node->api();
            $this->log("API connection established successfully!");
        }

        $this->log("Requesting node synchronization height...");
        $daemonHeight = $this->api->getDaemonHeight();
        $this->log("Result: $daemonHeight");

        $this->wallet->update(['daemon_height' => $daemonHeight]);

        return $this;
    }

    protected function openWallet(): self
    {
        $this->log("Opening wallet {$this->wallet->name}...");
        $this->api->openWallet($this->wallet->name, $this->wallet->password);
        $this->log('Wallet opened successfully!');

        $this->log("Requesting wallet synchronization height...");
        $walletHeight = $this->api->getHeight();
        $this->log("Result: $walletHeight");

        $this->wallet->update(['wallet_height' => $walletHeight]);

        return $this;
    }

    protected function getBalances(): self
    {
        $this->log('Requesting account list via get_accounts method...');
        $getAccounts = $this->api->getAccounts();
        $this->log('Success: '.json_encode($getAccounts));

        $balance = BigDecimal::of($getAccounts['total_balance'] ?: '0')->dividedBy(pow(10, 12), 12);
        $unlockedBalance = BigDecimal::of($getAccounts['total_unlocked_balance'] ?: '0')->dividedBy(pow(10, 12), 12);

        $this->wallet->update([
            'sync_at' => Date::now(),
            'balance' => $balance,
            'unlocked_balance' => $unlockedBalance,
        ]);

        foreach ($getAccounts['subaddress_accounts'] ?? [] as $item) {
            $balance = (BigDecimal::of($item['balance'] ?: '0'))->dividedBy(pow(10, 12), 12);
            $unlockedBalance = (BigDecimal::of($item['unlocked_balance'] ?: '0'))->dividedBy(pow(10, 12), 12);

            $account = $this->wallet
                ->accounts()
                ->updateOrCreate([
                    'base_address' => $item['base_address'],
                ], [
                    'account_index' => $item['account_index'],
                    'balance' => $balance,
                    'unlocked_balance' => $unlockedBalance,
                    'sync_at' => now(),
                ]);

            $this->log('Requesting address list for account '.$item['account_index'].' via get_address method...');
            $getAddress = $this->api->getAddress($item['account_index']);
            $this->log('Success: '.json_encode($getAddress));
            foreach( $getAddress['addresses'] ?? [] as $itemAddress ) {
                $this->wallet
                    ->addresses()
                    ->updateOrCreate([
                        'account_id' => $account->id,
                        'address' => $itemAddress['address'],
                    ], [
                        'address_index' => $itemAddress['address_index'],
                    ]);
            }
        }

        $this->wallet
            ->addresses()
            ->update([
                'balance' => 0,
                'unlocked_balance' => 0,
                'sync_at' => now(),
            ]);

        $this->log("Requesting all balances via get_balance method...");
        $getBalance = $this->api->getAllBalance();
        $this->log('Success: '.json_encode($getBalance));
        foreach( $getBalance['per_subaddress'] ?? [] as $item ) {
            $isOK = $this->wallet
                ->addresses()
                ->where('address', $item['address'])
                ->update([
                    'balance' => (BigDecimal::of($item['balance'] ?: '0'))->dividedBy(pow(10, 12), 12),
                    'unlocked_balance' => (BigDecimal::of($item['unlocked_balance'] ?: '0'))->dividedBy(pow(10, 12), 12),
                    'sync_at' => now(),
                ]);
            if( $isOK ) {
                $this->log('Balance for address '.$item['address'].' updated successfully!', 'success');
            }
        }

        return $this;
    }

    protected function incomingTransfers(): self
    {
        $this->log("Requesting incoming transfer history...");
        $getTransfers = $this->api->request(
            'get_transfers',
            [
                'in' => true,
                'out' => true,
                'pending' => true,
                'pool' => true,
                'all_accounts' => true
            ]
        );
        $this->log('History retrieved: '.json_encode($getTransfers));

        $transfers = array_merge($getTransfers['pool'] ?? [], $getTransfers['in'] ?? []);

        $rows = [];

        foreach ($transfers as $item) {
            $amount = (BigDecimal::of($item['amount'] ?: '0'))->dividedBy(pow(10, 12), 12);

            $address = $this->wallet
                ->addresses()
                ->whereAddress($item['address'])
                ->first();

            $deposit = $address?->deposits()->updateOrCreate([
                'txid' => $item['txid']
            ], [
                'wallet_id' => $this->wallet->id,
                'account_id' => $address->account_id,
                'amount' => $amount,
                'block_height' => ($item['height'] ?? 0) ?: null,
                'confirmations' => $item['confirmations'] ?? 0,
                'time_at' => Date::createFromTimestamp($item['timestamp']),
            ]);

            if ($deposit?->wasRecentlyCreated) {
                $this->webhooks[] = $deposit;
            }

            $rows[] = [
                'txid' => $item['txid'],
                'address' => $item['address'],
                'type' => $item['type'],
                'amount' => (string)$amount,
                'block_height' => ($item['height'] ?? 0) ?: null,
                'confirmations' => $item['confirmations'] ?? 0,
                'time_at' => Date::createFromTimestamp($item['timestamp']),
                'data' => json_encode($item),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        foreach( $getTransfers['out'] ?? [] as $item ) {
            $amount = (BigDecimal::of($item['amount'] ?: '0'))->dividedBy(pow(10, 12), 12);

            $rows[] = [
                'txid' => $item['txid'],
                'address' => $item['address'],
                'type' => $item['type'],
                'amount' => (string)$amount,
                'block_height' => ($item['height'] ?? 0) ?: null,
                'confirmations' => $item['confirmations'] ?? 0,
                'time_at' => Date::createFromTimestamp($item['timestamp']),
                'data' => json_encode($item),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        if( !empty($rows) ) {
            Monero::getModelTransaction()::upsert(
                $rows,
                ['txid', 'address'],
                ['type', 'amount', 'block_height', 'confirmations', 'time_at', 'data', 'updated_at']
            );
        }

        return $this;
    }

    protected function runWebhooks(): self
    {
        if ($this->webhookHandler) {
            foreach ($this->webhooks as $item) {
                try {
                    $this->log('Running Webhook for new Deposit ID#'.$item->id.'...');
                    $this->webhookHandler->handle($item);
                    $this->log('Webhook processed successfully!');
                } catch (\Exception $e) {
                    $this->log('Webhook processing error: '.$e->getMessage());
                    Log::error('Monero WebHook for deposit '.$item->id.' - '.$e->getMessage());
                }
            }
        }

        return $this;
    }
}