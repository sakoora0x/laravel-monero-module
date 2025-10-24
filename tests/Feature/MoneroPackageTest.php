<?php

use sakoora0x\LaravelMoneroModule\Facades\Monero;
use sakoora0x\LaravelMoneroModule\MoneroServiceProvider;

it('loads the service provider', function () {
    $providers = $this->app->getLoadedProviders();

    expect($providers)->toHaveKey(MoneroServiceProvider::class);
});

it('loads the monero config', function () {
    $config = config('monero');

    expect($config)->toBeArray()
        ->and($config)->toHaveKey('models')
        ->and($config)->toHaveKey('webhook_handler')
        ->and($config)->toHaveKey('atomic_lock');
});

it('registers the monero facade', function () {
    expect(Monero::getFacadeRoot())
        ->toBeInstanceOf(\sakoora0x\LaravelMoneroModule\Monero::class);
});

it('creates all database tables', function () {
    $this->artisan('migrate')->run();

    expect(\Schema::hasTable('monero_nodes'))->toBeTrue()
        ->and(\Schema::hasTable('monero_wallets'))->toBeTrue()
        ->and(\Schema::hasTable('monero_accounts'))->toBeTrue()
        ->and(\Schema::hasTable('monero_addresses'))->toBeTrue()
        ->and(\Schema::hasTable('monero_deposits'))->toBeTrue()
        ->and(\Schema::hasTable('monero_transactions'))->toBeTrue();
});

it('has correct columns in monero_wallets table', function () {
    $this->artisan('migrate')->run();

    expect(\Schema::hasColumn('monero_wallets', 'name'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'title'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'password'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'mnemonic'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'restore_height'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'balance'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_wallets', 'unlocked_balance'))->toBeTrue();
});

it('has correct columns in monero_accounts table', function () {
    $this->artisan('migrate')->run();

    expect(\Schema::hasColumn('monero_accounts', 'wallet_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_accounts', 'base_address'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_accounts', 'account_index'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_accounts', 'balance'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_accounts', 'unlocked_balance'))->toBeTrue();
});

it('has correct columns in monero_addresses table', function () {
    $this->artisan('migrate')->run();

    expect(\Schema::hasColumn('monero_addresses', 'wallet_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_addresses', 'account_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_addresses', 'address'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_addresses', 'address_index'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_addresses', 'balance'))->toBeTrue();
});

it('has correct columns in monero_deposits table', function () {
    expect(\Schema::hasColumn('monero_deposits', 'wallet_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_deposits', 'account_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_deposits', 'address_id'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_deposits', 'txid'))->toBeTrue()
        ->and(\Schema::hasColumn('monero_deposits', 'amount'))->toBeTrue();
});

it('model config returns correct model classes', function () {
    expect(Monero::getModelNode())->toContain('MoneroNode')
        ->and(Monero::getModelWallet())->toContain('MoneroWallet')
        ->and(Monero::getModelAccount())->toContain('MoneroAccount')
        ->and(Monero::getModelAddress())->toContain('MoneroAddress')
        ->and(Monero::getModelDeposit())->toContain('MoneroDeposit')
        ->and(Monero::getModelTransaction())->toContain('MoneroTransaction');
});
