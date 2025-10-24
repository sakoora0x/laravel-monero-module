# Laravel Monero Module Tests

This directory contains the test suite for the Laravel Monero Module using [Pest PHP](https://pestphp.com/).

## Test Structure

```
tests/
├── Feature/           # Integration tests
│   ├── Api/          # API client tests
│   └── ...           # Service and feature tests
├── Unit/             # Unit tests
│   ├── Casts/        # Custom cast tests
│   ├── DTO/          # Data Transfer Object tests
│   ├── Models/       # Model tests
│   └── Services/     # Service class tests
├── Pest.php          # Pest configuration
└── TestCase.php      # Base test case
```

## Running Tests

### Run all tests
```bash
composer test
```

### Run tests with coverage
```bash
composer test-coverage
```

### Run specific test file
```bash
./vendor/bin/pest tests/Unit/Models/MoneroWalletTest.php
```

### Run tests by name
```bash
./vendor/bin/pest --filter "can create a wallet"
```

### Run only unit tests
```bash
./vendor/bin/pest tests/Unit
```

### Run only feature tests
```bash
./vendor/bin/pest tests/Feature
```

## Test Coverage

The test suite covers:

- **Models**: All Eloquent models with their relationships, casts, and attributes
- **API Client**: Monero RPC API communication and error handling
- **Services**: Core service functionality and atomic locking
- **Casts**: Custom attribute casts like BigDecimalCast
- **DTOs**: Data transfer objects and their serialization
- **Integration**: Package installation, configuration, and migrations

## Writing Tests

Tests use Pest's expressive syntax:

```php
it('can create a wallet', function () {
    $wallet = MoneroWallet::create([
        'name' => 'test_wallet',
        'title' => 'Test Wallet',
    ]);

    expect($wallet)->toBeInstanceOf(MoneroWallet::class)
        ->and($wallet->name)->toBe('test_wallet');
});
```

## Dependencies

- `pestphp/pest`: Testing framework
- `pestphp/pest-plugin-laravel`: Laravel-specific Pest features
- `orchestra/testbench`: Laravel package testing utilities

## Configuration

- **phpunit.xml**: PHPUnit configuration with test suites and environment setup
- **Pest.php**: Pest-specific configuration and test case binding
- **TestCase.php**: Base test case with package setup
