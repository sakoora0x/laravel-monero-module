<?php

namespace Mollsoft\LaravelMoneroModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mollsoft\LaravelMoneroModule\Casts\BigDecimalCast;
use Mollsoft\LaravelMoneroModule\Facades\Monero;

class MoneroTransaction extends Model
{
    protected $fillable = [
        'txid',
        'address',
        'type',
        'amount',
        'block_height',
        'confirmations',
        'time_at',
        'data',
    ];

    protected $casts = [
        'amount' => BigDecimalCast::class,
        'confirmations' => 'integer',
        'time_at' => 'datetime',
        'data' => 'array',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Monero::getModelAddress(), 'address', 'address');
    }
}
