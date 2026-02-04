<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemMarketListing extends Model
{
    protected $fillable = [
        'seller_id',
        'item_id',
        'quantity',
        'price_per_unit',
        'total_price',
        'status',
        'description',
        'listed_at',
        'expires_at',
        'sold_at',
    ];

    protected $casts = [
        'listed_at' => 'datetime',
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the seller.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the transactions for this listing.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ItemMarketTransaction::class, 'listing_id');
    }

    /**
     * Scope to get active listings.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get expired listings.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }
}
