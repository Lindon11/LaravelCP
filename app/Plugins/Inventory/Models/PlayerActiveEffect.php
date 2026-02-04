<?php

namespace App\Plugins\Inventory\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class PlayerActiveEffect extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'effect_name',
        'stat',
        'value',
        'modifier_type',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope: Only active (not expired) effects
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: Filter by stat
     */
    public function scopeForStat($query, string $stat)
    {
        return $query->where('stat', $stat);
    }

    /**
     * Check if effect is still active
     */
    public function isActive(): bool
    {
        return $this->expires_at > now();
    }

    /**
     * Get remaining duration in seconds
     */
    public function getRemainingSecondsAttribute(): int
    {
        if (!$this->isActive()) return 0;
        return now()->diffInSeconds($this->expires_at);
    }

    /**
     * Get remaining duration formatted
     */
    public function getRemainingTimeAttribute(): string
    {
        $seconds = $this->remaining_seconds;
        if ($seconds <= 0) return 'Expired';

        if ($seconds < 60) return "{$seconds}s";
        if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
        return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
    }

    /**
     * Get total modifier for a player's stat
     */
    public static function getTotalModifier(int $userId, string $stat, string $modifierType = 'flat'): float
    {
        return self::where('user_id', $userId)
            ->where('stat', $stat)
            ->where('modifier_type', $modifierType)
            ->active()
            ->sum('value');
    }

    /**
     * Clean up expired effects
     */
    public static function cleanExpired(): int
    {
        return self::where('expires_at', '<=', now())->delete();
    }
}
