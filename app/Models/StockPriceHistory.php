<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockPriceHistory extends Model
{
    protected $fillable = [
        'stock_id',
        'price',
        'open_price',
        'close_price',
        'high_price',
        'low_price',
        'volume',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'open_price' => 'decimal:2',
        'close_price' => 'decimal:2',
        'high_price' => 'decimal:2',
        'low_price' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
