<?php

namespace App\Plugins\Alliances\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Territory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'location_id',
        'alliance_id',
        'owner_gang_id',
        'power_bonus',
        'income_bonus',
        'defense_bonus',
        'captured_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'captured_at' => 'datetime',
        'power_bonus' => 'integer',
        'income_bonus' => 'integer',
        'defense_bonus' => 'integer',
    ];

    /**
     * Owning alliance
     */
    public function alliance(): BelongsTo
    {
        return $this->belongsTo(Alliance::class);
    }

    /**
     * Owning gang
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Plugins\Gang\Models\Gang::class, 'owner_gang_id');
    }

    /**
     * Scope for unclaimed territories
     */
    public function scopeUnclaimed($query)
    {
        return $query->whereNull('alliance_id');
    }

    /**
     * Scope for claimed territories
     */
    public function scopeClaimed($query)
    {
        return $query->whereNotNull('alliance_id');
    }

    /**
     * Check if territory is contested
     */
    public function isContested(): bool
    {
        return TerritoryBattle::where('territory_id', $this->id)
            ->where('status', 'active')
            ->exists();
    }
}
