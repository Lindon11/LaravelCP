<?php

namespace App\Plugins\Stocks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'sector',
        'description',
        'current_price',
        'day_open',
        'day_high',
        'day_low',
        'shares_available',
        'shares_traded',
        'market_cap',
        'volatility',
        'is_active',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'day_open' => 'decimal:2',
        'day_high' => 'decimal:2',
        'day_low' => 'decimal:2',
        'market_cap' => 'decimal:2',
        'volatility' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function priceHistory(): HasMany
    {
        return $this->hasMany(StockPriceHistory::class);
    }

    public function userStocks(): HasMany
    {
        return $this->hasMany(UserStock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }
}
