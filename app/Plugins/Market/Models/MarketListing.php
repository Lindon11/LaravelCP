<?php

namespace App\Plugins\Market\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\Models\User;

class MarketListing extends Model
{
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'item_id',
        'quantity',
        'price',
        'current_bid',
        'type',
        'status',
        'expires_at',
        'sold_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sold_at' => 'datetime',
        'metadata' => 'array',
        'price' => 'integer',
        'current_bid' => 'integer',
        'quantity' => 'integer',
    ];

    /**
     * The seller
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * The buyer
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * The item (polymorphic or reference to items table)
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Plugins\Inventory\Models\Item::class, 'item_id');
    }

    /**
     * Auction bids
     */
    public function bids(): HasMany
    {
        return $this->hasMany(MarketBid::class, 'listing_id');
    }

    /**
     * Scope for active listings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for auctions
     */
    public function scopeAuctions($query)
    {
        return $query->where('type', 'auction');
    }

    /**
     * Scope for fixed price
     */
    public function scopeFixed($query)
    {
        return $query->where('type', 'fixed');
    }

    /**
     * Check if listing is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get current price (for auctions, this is current bid)
     */
    public function getCurrentPrice(): int
    {
        if ($this->type === 'auction' && $this->current_bid) {
            return $this->current_bid;
        }
        return $this->price;
    }
}
