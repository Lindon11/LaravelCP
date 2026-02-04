<?php

namespace App\Plugins\Tournament\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class TournamentParticipant extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'seed',
        'wins',
        'losses',
        'points',
        'eliminated',
        'eliminated_round',
        'final_placement',
        'registered_at',
    ];

    protected $casts = [
        'eliminated' => 'boolean',
        'registered_at' => 'datetime',
        'wins' => 'integer',
        'losses' => 'integer',
        'points' => 'integer',
        'seed' => 'integer',
        'eliminated_round' => 'integer',
        'final_placement' => 'integer',
    ];

    /**
     * The tournament
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * The user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active participants
     */
    public function scopeActive($query)
    {
        return $query->where('eliminated', false);
    }

    /**
     * Scope for eliminated
     */
    public function scopeEliminated($query)
    {
        return $query->where('eliminated', true);
    }

    /**
     * Eliminate participant
     */
    public function eliminate(int $round, int $placement): void
    {
        $this->update([
            'eliminated' => true,
            'eliminated_round' => $round,
            'final_placement' => $placement,
        ]);
    }
}
