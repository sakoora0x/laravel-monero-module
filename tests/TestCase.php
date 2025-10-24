<?php

namespace sakoora0x\LaravelMoneroModule\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use sakoora0x\LaravelMoneroModule\MoneroServiceProvider;
use sakoora0x\LaravelMoneroModule\Tests\Helpers\CreatesModels;

class TestCase extends Orchestra
{
    use RefreshDatabase, CreatesModels;

    protected function getPackageProviders($app): array
    {
        return [
            MoneroServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Set APP_KEY for encryption
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // Load package config
        $app['config']->set('monero', require __DIR__ . '/../config/monero.php');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
