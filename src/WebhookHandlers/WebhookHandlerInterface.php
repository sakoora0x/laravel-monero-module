<?php

namespace sakoora0x\LaravelMoneroModule\WebhookHandlers;

use sakoora0x\LaravelMoneroModule\Models\MoneroDeposit;

interface WebhookHandlerInterface
{
    public function handle(MoneroDeposit $deposit): void;
}