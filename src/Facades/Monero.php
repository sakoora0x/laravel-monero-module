<?php

namespace sakoora0x\LaravelMoneroModule\Facades;

use Illuminate\Support\Facades\Facade;

class Monero extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \sakoora0x\LaravelMoneroModule\Monero::class;
    }
}
