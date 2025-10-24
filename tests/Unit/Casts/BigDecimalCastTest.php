<?php

use Brick\Math\BigDecimal;
use sakoora0x\LaravelMoneroModule\Casts\BigDecimalCast;

it('can cast string to BigDecimal', function () {
    $wallet = $this->createWallet([
        'balance' => '1000000000000',
    ]);

    expect($wallet->balance)->toBeInstanceOf(BigDecimal::class)
        ->and($wallet->balance->__toString())->toBe('1000000000000');
});

it('handles zero values', function () {
    $wallet = $this->createWallet([
        'balance' => '0',
    ]);

    expect($wallet->balance)->toBeInstanceOf(BigDecimal::class)
        ->and($wallet->balance->__toString())->toBe('0');
});

it('handles null values as zero', function () {
    $wallet = $this->createWallet([
        'balance' => null,
    ]);

    expect($wallet->balance)->toBeInstanceOf(BigDecimal::class)
        ->and($wallet->balance->__toString())->toBe('0');
});

it('can set BigDecimal values', function () {
    $wallet = $this->createWallet();

    $bigDecimal = BigDecimal::of('5000000000000');
    $wallet->balance = $bigDecimal;
    $wallet->save();

    $wallet->refresh();

    expect($wallet->balance)->toBeInstanceOf(BigDecimal::class)
        ->and($wallet->balance->__toString())->toBe('5000000000000');
});

it('can set string values', function () {
    $wallet = $this->createWallet();

    $wallet->balance = '7500000000000';
    $wallet->save();

    $wallet->refresh();

    expect($wallet->balance)->toBeInstanceOf(BigDecimal::class)
        ->and($wallet->balance->__toString())->toBe('7500000000000');
});

it('handles large numbers correctly', function () {
    $largeNumber = '999999999999999999999';

    $wallet = $this->createWallet([
        'balance' => $largeNumber,
    ]);

    expect($wallet->balance->__toString())->toBe($largeNumber);
});

it('can perform arithmetic operations', function () {
    $wallet = $this->createWallet([
        'balance' => '1000000000000',
        'unlocked_balance' => '500000000000',
    ]);

    $locked = $wallet->balance->minus($wallet->unlocked_balance);

    expect($locked->__toString())->toBe('500000000000');
});
