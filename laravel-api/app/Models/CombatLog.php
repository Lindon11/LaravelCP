<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatLog extends Model
{
    protected $fillable = [
        'attacker_id',
        'defender_id',
        'damage_dealt',
        'attacker_health_before',
        'attacker_health_after',
        'defender_health_before',
        'defender_health_after',
        'weapon_used',
        'outcome',
        'respect_gained',
        'cash_stolen',
        'defender_in_hospital',
    ];

    protected $casts = [
        'defender_in_hospital' => 'boolean',
    ];

    public function attacker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attacker_id');
    }

    public function defender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'defender_id');
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'weapon_used');
    }
}
