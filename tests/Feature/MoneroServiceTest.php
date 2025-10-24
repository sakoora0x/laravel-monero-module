<?php

use sakoora0x\LaravelMoneroModule\Facades\Monero;
use sakoora0x\LaravelMoneroModule\Models\MoneroNode;
use sakoora0x\LaravelMoneroModule\Models\MoneroWallet;
use Illuminate\Support\Facades\Cache;


it('can get model classes from config', function () {
    expect(Monero::getModelNode())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroNode::class)
        ->and(Monero::getModelWallet())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroWallet::class)
        ->and(Monero::getModelAccount())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroAccount::class)
        ->and(Monero::getModelAddress())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroAddress::class)
        ->and(Monero::getModelDeposit())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroDeposit::class)
        ->and(Monero::getModelTransaction())->toBe(\sakoora0x\LaravelMoneroModule\Models\MoneroTransaction::class);
});

it('can use atomic locks', function () {
    $executed = false;

    $result = Monero::atomicLock('test_lock', function() use (&$executed) {
        $executed = true;
        return 'result';
    });

    expect($result)->toBe('result')
        ->and($executed)->toBeTrue();
});

it('can use node atomic locks for remote nodes', function () {
    $node = $this->createNode([
        'host' => 'remote.example.com',
    ]);

    $executed = false;

    $result = Monero::nodeAtomicLock($node, function() use (&$executed) {
        $executed = true;
        return 'node-result';
    });

    expect($result)->toBe('node-result')
        ->and($executed)->toBeTrue();
});

it('bypasses locks for local nodes', function () {
    $node = $this->createNode([
        'local' => true,
    ]);

    $executed = false;

    $result = Monero::nodeAtomicLock($node, function() use (&$executed) {
        $executed = true;
        return 'direct-call';
    });

    expect($result)->toBe('direct-call')
        ->and($executed)->toBeTrue();
});

it('can use wallet atomic locks', function () {
    $wallet = $this->createWallet();

    $executed = false;

    $result = Monero::walletAtomicLock($wallet, function() use (&$executed) {
        $executed = true;
        return 'wallet-result';
    });

    expect($result)->toBe('wallet-result')
        ->and($executed)->toBeTrue();
});

it('monero facade is properly configured', function () {
    expect(Monero::getFacadeRoot())
        ->toBeInstanceOf(\sakoora0x\LaravelMoneroModule\Monero::class);
});
