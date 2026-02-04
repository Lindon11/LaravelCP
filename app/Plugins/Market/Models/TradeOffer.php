<?php

namespace App\Plugins\Market\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class TradeOffer extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'sender_items',
        'sender_money',
        'recipient_items',
        'recipient_money',
        'message',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'sender_items' => 'array',
        'recipient_items' => 'array',
        'sender_money' => 'integer',
        'recipient_money' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Trade sender
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Trade recipient
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Scope for pending trades
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Check if trade is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
