<?php

use sakoora0x\LaravelMoneroModule\Models\MoneroWallet;
use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;
use sakoora0x\LaravelMoneroModule\Models\MoneroAddress;
use sakoora0x\LaravelMoneroModule\Models\MoneroDeposit;
use sakoora0x\LaravelMoneroModule\Models\MoneroNode;


it('can create a wallet', function () {
    $wallet = $this->createWallet([
        'password' => 'test-password',
        'mnemonic' => 'test mnemonic phrase here',
    ]);

    expect($wallet)->toBeInstanceOf(MoneroWallet::class)
        ->and($wallet->title)->toBe('Test Wallet')
        ->and($wallet->restore_height)->toBe(0);
});

it('encrypts sensitive fields', function () {
        $wallet = $this->createWallet([
        'password' => 'my-secret-password',
        'mnemonic' => 'secret mnemonic phrase',
    ]);

    // Password and mnemonic should be encrypted in database
    $rawData = \DB::table('monero_wallets')->where('id', $wallet->id)->first();

    expect($rawData->password)->not()->toBe('my-secret-password')
        ->and($rawData->mnemonic)->not()->toBe('secret mnemonic phrase');
});

it('hides sensitive fields from array output', function () {
        $wallet = $this->createWallet([
        'password' => 'test-password',
        'mnemonic' => 'test mnemonic',
    ]);

    $array = $wallet->toArray();

    expect($array)->not()->toHaveKey('password')
        ->and($array)->not()->toHaveKey('mnemonic');
});

it('has a node relationship', function () {
    $node = $this->createNode();

    $wallet = $this->createWallet(['node_id' => $node->id]);

    expect($wallet->node)->toBeInstanceOf(MoneroNode::class)
        ->and($wallet->node->id)->toBe($node->id);
});

it('has accounts relationship', function () {
    $wallet = $this->createWallet();

    MoneroAccount::create([
        'wallet_id' => $wallet->id,
        'base_address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'account_index' => 0,
    ]);

    expect($wallet->accounts)->toHaveCount(1)
        ->and($wallet->accounts->first())->toBeInstanceOf(MoneroAccount::class);
});

it('has addresses relationship', function () {
    $wallet = $this->createWallet();

    $account = MoneroAccount::create([
        'wallet_id' => $wallet->id,
        'base_address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'account_index' => 0,
    ]);

    MoneroAddress::create([
        'wallet_id' => $wallet->id,
        'account_id' => $account->id,
        'address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'address_index' => 0,
    ]);

    expect($wallet->addresses)->toHaveCount(1)
        ->and($wallet->addresses->first())->toBeInstanceOf(MoneroAddress::class);
});

it('has deposits relationship', function () {
    $wallet = $this->createWallet();

    $account = MoneroAccount::create([
        'wallet_id' => $wallet->id,
        'base_address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'account_index' => 0,
    ]);

    $address = MoneroAddress::create([
        'wallet_id' => $wallet->id,
        'account_id' => $account->id,
        'address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'address_index' => 0,
    ]);

    MoneroDeposit::create([
        'wallet_id' => $wallet->id,
        'account_id' => $account->id,
        'address_id' => $address->id,
        'txid' => 'test_tx_id_123',
        'amount' => '1000000000000',
        'confirmations' => 10,
        'time_at' => now(),
    ]);

    expect($wallet->deposits)->toHaveCount(1)
        ->and($wallet->deposits->first())->toBeInstanceOf(MoneroDeposit::class);
});

it('can unlock wallet with password', function () {
        $wallet = $this->createWallet(['password' => 'test-password']);

    $wallet->unlockWallet('test-password');

    expect($wallet->plain_password)->toBe('test-password');
});

it('returns null for plain password when not unlocked', function () {
        $wallet = $this->createWallet(['password' => 'test-password']);

    expect($wallet->plain_password)->toBeNull();
});

it('casts balance fields to big decimal', function () {
        $wallet = $this->createWallet([
        'balance' => '1000000000000',
        'unlocked_balance' => '500000000000',
    ]);

    expect($wallet->balance->__toString())->toBe('1000000000000')
        ->and($wallet->unlocked_balance->__toString())->toBe('500000000000');
});

it('casts touch_at to datetime', function () {
        $wallet = $this->createWallet(['touch_at' => now()]);

    expect($wallet->touch_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('has primary account relationship', function () {
    $wallet = $this->createWallet();

    MoneroAccount::create([
        'wallet_id' => $wallet->id,
        'base_address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx1',
        'account_index' => 0,
    ]);

    MoneroAccount::create([
        'wallet_id' => $wallet->id,
        'base_address' => '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx2',
        'account_index' => 1,
    ]);

    expect($wallet->primaryAccount)->toBeInstanceOf(MoneroAccount::class)
        ->and($wallet->primaryAccount->account_index)->toBe(0);
});
