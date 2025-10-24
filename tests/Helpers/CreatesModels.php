<?php

namespace sakoora0x\LaravelMoneroModule\Tests\Helpers;

use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;
use sakoora0x\LaravelMoneroModule\Models\MoneroAddress;
use sakoora0x\LaravelMoneroModule\Models\MoneroNode;
use sakoora0x\LaravelMoneroModule\Models\MoneroWallet;

trait CreatesModels
{
    protected function createNode(array $attributes = []): MoneroNode
    {
        return MoneroNode::create(array_merge([
            'name' => 'test_node_' . uniqid(),
            'title' => 'Test Node',
            'scheme' => 'http',
            'host' => 'localhost',
            'port' => 18081,
        ], $attributes));
    }

    protected function createWallet(array $attributes = []): MoneroWallet
    {
        if (!isset($attributes['node_id'])) {
            $node = $this->createNode();
            $attributes['node_id'] = $node->id;
        }

        return MoneroWallet::create(array_merge([
            'name' => 'test_wallet_' . uniqid(),
            'title' => 'Test Wallet',
            'restore_height' => 0,
        ], $attributes));
    }

    protected function createAccount(array $attributes = []): MoneroAccount
    {
        if (!isset($attributes['wallet_id'])) {
            $wallet = $this->createWallet();
            $attributes['wallet_id'] = $wallet->id;
        }

        return MoneroAccount::create(array_merge([
            'base_address' => '4' . str_repeat('x', 94),
            'account_index' => 0,
        ], $attributes));
    }

    protected function createAddress(array $attributes = []): MoneroAddress
    {
        if (!isset($attributes['wallet_id']) || !isset($attributes['account_id'])) {
            $account = $this->createAccount();
            $attributes['wallet_id'] = $account->wallet_id;
            $attributes['account_id'] = $account->id;
        }

        if (!isset($attributes['address'])) {
            $attributes['address'] = '4' . str_pad(uniqid(), 94, 'x', STR_PAD_LEFT);
        }

        return MoneroAddress::create(array_merge([
            'address_index' => 0,
        ], $attributes));
    }
}
