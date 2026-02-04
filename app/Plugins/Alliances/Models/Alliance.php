<?php

namespace App\Plugins\Alliances\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Plugins\Gang\Models\Gang;

class Alliance extends Model
{
    protected $fillable = [
        'name',
        'tag',
        'description',
        'leader_gang_id',
        'power',
        'treasury',
        'emblem',
        'color',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'power' => 'integer',
        'treasury' => 'integer',
    ];

    /**
     * Alliance leader gang
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Gang::class, 'leader_gang_id');
    }

    /**
     * Member gangs
     */
    public function gangs(): HasMany
    {
        return $this->hasMany(AllianceMember::class);
    }

    /**
     * Controlled territories
     */
    public function territories(): HasMany
    {
        return $this->hasMany(Territory::class);
    }

    /**
     * Wars as attacker
     */
    public function warsAsAttacker(): HasMany
    {
        return $this->hasMany(AllianceWar::class, 'attacker_id');
    }

    /**
     * Wars as defender
     */
    public function warsAsDefender(): HasMany
    {
        return $this->hasMany(AllianceWar::class, 'defender_id');
    }

    /**
     * All wars
     */
    public function wars()
    {
        return AllianceWar::where('attacker_id', $this->id)
            ->orWhere('defender_id', $this->id);
    }

    /**
     * Calculate total power
     */
    public function calculatePower(): int
    {
        $gangPower = $this->gangs()->with('gang')->get()->sum(function ($member) {
            return $member->gang->power ?? 0;
        });

        $territoryPower = $this->territories->sum('power_bonus');

        return $gangPower + $territoryPower;
    }

    /**
     * Update power
     */
    public function updatePower(): void
    {
        $this->update(['power' => $this->calculatePower()]);
    }
}
