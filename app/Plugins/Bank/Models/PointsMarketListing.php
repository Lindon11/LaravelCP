<?php

namespace App\Plugins\Bank\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointsMarketListing extends Model
{
    protected $fillable = [
        'seller_id',
        'points_amount',
        'cash_price',
        'rate',
        'status',
        'listed_at',
        'sold_at',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'listed_at' => 'datetime',
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
     * Get the transactions for this listing.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class, 'listing_id');
    }

    /**
     * Scope to get active listings.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
