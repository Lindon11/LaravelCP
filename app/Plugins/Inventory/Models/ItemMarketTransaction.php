<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMarketTransaction extends Model
{
    protected $fillable = [
        'listing_id',
        'seller_id',
        'buyer_id',
        'item_id',
        'quantity',
        'price_per_unit',
        'total_price',
        'market_fee',
        'seller_receives',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the listing.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(ItemMarketListing::class, 'listing_id');
    }

    /**
     * Get the seller.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the buyer.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
