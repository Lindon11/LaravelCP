<?php

namespace App\Plugins\Alliances\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllianceWar extends Model
{
    protected $fillable = [
        'attacker_id',
        'defender_id',
        'reason',
        'status',
        'attacker_score',
        'defender_score',
        'started_at',
        'ends_at',
        'winner_id',
        'rewards',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'rewards' => 'array',
        'attacker_score' => 'integer',
        'defender_score' => 'integer',
    ];

    /**
     * Attacking alliance
     */
    public function attacker(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'attacker_id');
    }

    /**
     * Defending alliance
     */
    public function defender(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'defender_id');
    }

    /**
     * Winner alliance
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Alliance::class, 'winner_id');
    }

    /**
     * War battles
     */
    public function battles(): HasMany
    {
        return $this->hasMany(WarBattle::class, 'war_id');
    }

    /**
     * Scope for active wars
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if war has ended
     */
    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Determine winner based on scores
     */
    public function determineWinner(): ?int
    {
        if ($this->attacker_score > $this->defender_score) {
            return $this->attacker_id;
        } elseif ($this->defender_score > $this->attacker_score) {
            return $this->defender_id;
        }
        return null; // Draw
    }
}
