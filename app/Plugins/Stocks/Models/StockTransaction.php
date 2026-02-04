<?php

namespace App\Plugins\Stocks\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'stock_id',
        'type',
        'shares',
        'price_per_share',
        'total_amount',
        'fees',
        'executed_at',
    ];

    protected $casts = [
        'price_per_share' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'fees' => 'decimal:2',
        'executed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
