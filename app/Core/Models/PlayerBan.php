<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBan extends Model
{
    protected $fillable = [
        'user_id',
        'banned_by',
        'type',
        'reason',
        'banned_at',
        'expires_at',
        'unbanned_at',
        'unbanned_by',
        'unban_reason',
        'is_active',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'unbanned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function unbannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unbanned_by');
    }

    public function isExpired(): bool
    {
        if ($this->type === 'permanent') {
            return false;
        }

        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->where('type', 'permanent')
                  ->orWhere(function($q2) {
                      $q2->where('type', 'temporary')
                         ->where('expires_at', '>', now());
                  });
            });
    }
}
