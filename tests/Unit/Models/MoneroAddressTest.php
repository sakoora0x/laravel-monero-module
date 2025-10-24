<?php

use sakoora0x\LaravelMoneroModule\Models\MoneroAddress;
use sakoora0x\LaravelMoneroModule\Models\MoneroDeposit;
use sakoora0x\LaravelMoneroModule\Models\MoneroTransaction;

it('can create an address', function () {
    $address = $this->createAddress([
        'title' => 'Primary Address',
    ]);

    expect($address)->toBeInstanceOf(MoneroAddress::class)
        ->and($address->address_index)->toBe(0);
});

it('belongs to a wallet', function () {
    $address = $this->createAddress();

    expect($address->wallet)->not()->toBeNull()
        ->and($address->wallet->id)->toBe($address->wallet_id);
});

it('belongs to an account', function () {
    $address = $this->createAddress();

    expect($address->account)->not()->toBeNull()
        ->and($address->account->id)->toBe($address->account_id);
});

it('has deposits relationship', function () {
    $address = $this->createAddress();

    MoneroDeposit::create([
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

    expect($address->deposits)->toHaveCount(1)
        ->and($address->deposits->first())->toBeInstanceOf(MoneroDeposit::class);
});

it('has transactions relationship', function () {
    $address = $this->createAddress();

    MoneroTransaction::create([
        'address' => $address->address,
        'txid' => 'hash123',
        'type' => 'in',
        'amount' => '1000000000000',
        'confirmations' => 10,
        'time_at' => now(),
        'data' => json_encode([]),
    ]);

    expect($address->transactions)->toHaveCount(1)
        ->and($address->transactions->first())->toBeInstanceOf(MoneroTransaction::class);
});

it('casts balance fields correctly', function () {
    $address = $this->createAddress([
        'balance' => '2000000000000',
        'unlocked_balance' => '1800000000000',
    ]);

    expect($address->balance->__toString())->toBe('2000000000000')
        ->and($address->unlocked_balance->__toString())->toBe('1800000000000');
});

it('casts available to boolean', function () {
    $address = $this->createAddress([
        'available' => false,
    ]);

    expect($address->available)->toBeFalse();
});

it('casts sync_at to datetime', function () {
    $address = $this->createAddress([
        'sync_at' => now(),
    ]);

    expect($address->sync_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
