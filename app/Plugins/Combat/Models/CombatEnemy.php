<?php

namespace App\Plugins\Combat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatEnemy extends Model
{
    protected $fillable = [
        'area_id',
        'name',
        'icon',
        'description',
        'level',
        'health',
        'max_health',
        'strength',
        'defense',
        'speed',
        'agility',
        'weakness',
        'difficulty',
        'experience_reward',
        'cash_reward_min',
        'cash_reward_max',
        'spawn_rate',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'spawn_rate' => 'decimal:2',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(CombatArea::class, 'area_id');
    }
}
