<?php

namespace App\Plugins\DailyRewards\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReward extends Model
{
    protected $fillable = [
        'user_id',
        'streak',
        'last_claimed_at',
    ];

    protected $casts = [
        'last_claimed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canClaim(): bool
    {
        if (!$this->last_claimed_at) {
            return true;
        }

        return $this->last_claimed_at->lt(now()->startOfDay());
    }

    public function shouldResetStreak(): bool
    {
        if (!$this->last_claimed_at) {
            return false;
        }

        return $this->last_claimed_at->lt(now()->subDay()->startOfDay());
    }
}
