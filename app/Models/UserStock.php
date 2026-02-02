<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStock extends Model
{
    protected $fillable = [
        'user_id',
        'stock_id',
        'shares',
        'average_buy_price',
        'total_invested',
        'current_value',
        'profit_loss',
    ];

    protected $casts = [
        'average_buy_price' => 'decimal:2',
        'total_invested' => 'decimal:2',
        'current_value' => 'decimal:2',
        'profit_loss' => 'decimal:2',
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
