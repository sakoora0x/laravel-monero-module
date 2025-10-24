<?php

namespace sakoora0x\LaravelMoneroModule;

use sakoora0x\LaravelMoneroModule\Commands\MoneroCommand;
use sakoora0x\LaravelMoneroModule\Commands\MoneroNodeSyncCommand;
use sakoora0x\LaravelMoneroModule\Commands\MoneroSyncCommand;
use sakoora0x\LaravelMoneroModule\Commands\MoneroWalletRPCCommand;
use sakoora0x\LaravelMoneroModule\Commands\MoneroWalletSyncCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MoneroServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('monero')
            ->hasConfigFile()
            ->discoversMigrations()
            ->hasCommands([
                MoneroCommand::class,
                MoneroSyncCommand::class,
                MoneroWalletSyncCommand::class,
                MoneroWalletRPCCommand::class,
                MoneroNodeSyncCommand::class,
            ])
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('sakoora0x/laravel-monero-module');
            });

        $this->app->singleton(Monero::class);
    }
}