<?php

namespace sakoora0x\LaravelMoneroModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use sakoora0x\LaravelEthereumModule\Enums\EthereumModel;
use sakoora0x\LaravelEthereumModule\Facades\Ethereum;
use sakoora0x\LaravelEthereumModule\Models\EthereumAddress;
use sakoora0x\LaravelEthereumModule\Models\EthereumTransaction;
use sakoora0x\LaravelMoneroModule\Casts\BigDecimalCast;
use sakoora0x\LaravelMoneroModule\Facades\Monero;

class MoneroAccount extends Model
{
    protected $fillable = [
        'wallet_id',
        'base_address',
        'title',
        'account_index',
        'balance',
        'unlocked_balance',
        'sync_at',
        'available',
    ];

    protected $casts = [
        'account_index' => 'integer',
        'balance' => BigDecimalCast::class,
        'unlocked_balance' => BigDecimalCast::class,
        'sync_at' => 'datetime',
        'available' => 'boolean',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Monero::getModelWallet(), 'wallet_id');
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(Monero::getModelAddress(), 'account_id')
            ->ofMany('address_index', 'min');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Monero::getModelAddress(), 'account_id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Monero::getModelDeposit(), 'account_id');
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Monero::getModelTransaction(),
            Monero::getModelAddress(),
            'account_id',
            'address',
            'id',
            'address'
        );
    }
}
