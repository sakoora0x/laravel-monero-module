<?php

use sakoora0x\LaravelMoneroModule\DTO\BIP39Convert;

it('can create a BIP39Convert instance', function () {
    $bip39 = new BIP39Convert(
        '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'spend-key-hex',
        'view-key-hex',
        'test mnemonic phrase'
    );

    expect($bip39)->toBeInstanceOf(BIP39Convert::class)
        ->and($bip39->address)->toBe('4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
        ->and($bip39->spendKey)->toBe('spend-key-hex')
        ->and($bip39->viewKey)->toBe('view-key-hex')
        ->and($bip39->mnemonic)->toBe('test mnemonic phrase');
});

it('is readonly', function () {
    $bip39 = new BIP39Convert(
        '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'spend-key-hex',
        'view-key-hex',
        'test mnemonic phrase'
    );

    expect(fn() => $bip39->address = 'new-address')
        ->toThrow(\Error::class);
});

it('can convert to array', function () {
    $bip39 = new BIP39Convert(
        '4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'spend-key-hex',
        'view-key-hex',
        'test mnemonic phrase'
    );

    $array = $bip39->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('address')
        ->and($array)->toHaveKey('spendKey')
        ->and($array)->toHaveKey('viewKey')
        ->and($array)->toHaveKey('mnemonic')
        ->and($array['address'])->toBe('4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx')
        ->and($array['spendKey'])->toBe('spend-key-hex')
        ->and($array['viewKey'])->toBe('view-key-hex')
        ->and($array['mnemonic'])->toBe('test mnemonic phrase');
});

it('maintains data integrity', function () {
    $address = '48xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    $spendKey = 'a'.str_repeat('0', 63);
    $viewKey = 'b'.str_repeat('0', 63);
    $mnemonic = 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about';

    $bip39 = new BIP39Convert($address, $spendKey, $viewKey, $mnemonic);

    expect($bip39->address)->toBe($address)
        ->and($bip39->spendKey)->toBe($spendKey)
        ->and($bip39->viewKey)->toBe($viewKey)
        ->and($bip39->mnemonic)->toBe($mnemonic);
});

it('includes all fields in array output', function () {
    $bip39 = new BIP39Convert(
        'address',
        'spend',
        'view',
        'mnemonic'
    );

    $array = $bip39->toArray();

    expect($array)->toHaveKeys(['address', 'spendKey', 'viewKey', 'mnemonic']);
});
