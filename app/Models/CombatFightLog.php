<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatFightLog extends Model
{
    protected $fillable = [
        'fight_id',
        'attacker_type',
        'damage',
        'critical',
        'missed',
        'weapon_used',
        'message',
    ];

    protected $casts = [
        'critical' => 'boolean',
        'missed' => 'boolean',
    ];

    public function fight(): BelongsTo
    {
        return $this->belongsTo(CombatFight::class, 'fight_id');
    }
}
