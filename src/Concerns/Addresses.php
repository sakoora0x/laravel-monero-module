<?php

namespace sakoora0x\LaravelMoneroModule\Concerns;

use sakoora0x\LaravelMoneroModule\Facades\Monero;
use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;
use sakoora0x\LaravelMoneroModule\Models\MoneroAddress;
use sakoora0x\LaravelMoneroModule\Models\MoneroNode;

trait Addresses
{
    public function createAddress(MoneroAccount $account, ?string $title = null): MoneroAddress
    {
        return Monero::generalAtomicLock($account->wallet, function () use ($account, $title) {
            $wallet = $account->wallet;
            $api = $wallet->node->api();

            if( !$wallet->node->isLocal() ) {
                $api->openWallet($wallet->name, $wallet->password);
            }

            $createAddress = $api->createAddress($account->account_index);

            return $account->addresses()->create([
                'wallet_id' => $wallet->id,
                'address' => $createAddress['address'],
                'address_index' => $createAddress['address_index'],
                'title' => $title,
            ]);
        });
    }

    public function validateAddress(MoneroNode $node, string $address): bool
    {
        $api = $node->api();

        $details = $api->validateAddress($address);

        return (bool)$details['valid'];
    }
}
