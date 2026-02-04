<?php

namespace App\Plugins\Market\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class MarketBid extends Model
{
    protected $fillable = [
        'listing_id',
        'bidder_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * The listing
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketListing::class, 'listing_id');
    }

    /**
     * The bidder
     */
    public function bidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_id');
    }
}
