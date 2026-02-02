<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CombatFight extends Model
{
    protected $fillable = [
        'user_id',
        'enemy_id',
        'area_id',
        'enemy_health',
        'enemy_max_health',
        'player_health_start',
        'started_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enemy(): BelongsTo
    {
        return $this->belongsTo(CombatEnemy::class, 'enemy_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(CombatArea::class, 'area_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CombatFightLog::class, 'fight_id');
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }
}
