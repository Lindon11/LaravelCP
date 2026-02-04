<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpBan extends Model
{
    protected $fillable = [
        'ip_address',
        'banned_by',
        'reason',
        'banned_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public static function isIpBanned(string $ip): bool
    {
        return static::active()
            ->where('ip_address', $ip)
            ->exists();
    }
}
