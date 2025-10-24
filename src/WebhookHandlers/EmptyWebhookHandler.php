<?php

namespace sakoora0x\LaravelMoneroModule\WebhookHandlers;

use sakoora0x\LaravelMoneroModule\Models\MoneroAddress;
use Illuminate\Support\Facades\Log;
use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;
use sakoora0x\LaravelMoneroModule\Models\MoneroDeposit;
use sakoora0x\LaravelMoneroModule\Models\MoneroIntegratedAddress;
use sakoora0x\LaravelMoneroModule\Models\MoneroWallet;

class EmptyWebhookHandler implements WebhookHandlerInterface
{
    public function handle(MoneroDeposit $deposit): void {
        Log::error('Monero Wallet '.$deposit->wallet->name.', account '.$deposit->account->base_address.', address '.$deposit->address->address);
    }
}