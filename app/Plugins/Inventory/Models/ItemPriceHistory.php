<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPriceHistory extends Model
{
    protected $table = 'item_price_history';

    protected $fillable = [
        'item_id',
        'average_price',
        'lowest_price',
        'highest_price',
        'total_sold',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
