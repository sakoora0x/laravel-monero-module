<?php

use sakoora0x\LaravelMoneroModule\Models\MoneroAccount;

it('can create an account', function () {
    $account = $this->createAccount([
        'title' => 'Primary Account',
        'balance' => '1000000000000',
        'unlocked_balance' => '900000000000',
    ]);

    expect($account)->toBeInstanceOf(MoneroAccount::class)
        ->and($account->account_index)->toBe(0)
        ->and($account->balance->__toString())->toBe('1000000000000');
});

it('belongs to a wallet', function () {
    $account = $this->createAccount();

    expect($account->wallet)->not()->toBeNull()
        ->and($account->wallet->id)->toBe($account->wallet_id);
});

it('has addresses relationship', function () {
    $account = $this->createAccount();

    $this->createAddress([
        'wallet_id' => $account->wallet_id,
        'account_id' => $account->id,
        'address' => '4' . str_repeat('x', 94) . '1',
        'address_index' => 0,
    ]);

    $this->createAddress([
        'wallet_id' => $account->wallet_id,
        'account_id' => $account->id,
        'address' => '4' . str_repeat('x', 94) . '2',
        'address_index' => 1,
    ]);

    expect($account->addresses)->toHaveCount(2);
});

it('has primary address relationship', function () {
    $account = $this->createAccount();

    $this->createAddress([
        'wallet_id' => $account->wallet_id,
        'account_id' => $account->id,
        'address_index' => 0,
    ]);

    $this->createAddress([
        'wallet_id' => $account->wallet_id,
        'account_id' => $account->id,
        'address_index' => 1,
    ]);

    expect($account->primaryAddress)->not()->toBeNull()
        ->and($account->primaryAddress->address_index)->toBe(0);
});

it('has deposits relationship', function () {
    $address = $this->createAddress();

    \sakoora0x\LaravelMoneroModule\Models\MoneroDeposit::create([
        'wallet_id' => $address->wallet_id,
        'account_id' => $address->account_id,
        'address_id' => $address->id,
        'txid' => 'test_tx_id_123',
        'amount' => '1000000000000',
        'confirmations' => 10,
        'time_at' => now(),
        'confirmations' => 10,
        'time_at' => now(),
    ]);

    expect($address->account->deposits)->toHaveCount(1);
});

it('casts balance fields correctly', function () {
    $account = $this->createAccount([
        'balance' => '1500000000000',
        'unlocked_balance' => '1200000000000',
    ]);

    expect($account->balance->__toString())->toBe('1500000000000')
        ->and($account->unlocked_balance->__toString())->toBe('1200000000000');
});

it('casts available field to boolean', function () {
    $account = $this->createAccount([
        'available' => true,
    ]);

    expect($account->available)->toBeTrue();
});

it('casts sync_at to datetime', function () {
    $account = $this->createAccount([
        'sync_at' => now(),
    ]);

    expect($account->sync_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
