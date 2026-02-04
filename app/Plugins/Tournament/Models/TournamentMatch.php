<?php

namespace App\Plugins\Tournament\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class TournamentMatch extends Model
{
    protected $fillable = [
        'tournament_id',
        'round',
        'match_number',
        'bracket_position',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'loser_id',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
        'next_match_id',
        'combat_log',
        'metadata',
    ];

    protected $casts = [
        'combat_log' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'round' => 'integer',
        'match_number' => 'integer',
        'player1_score' => 'integer',
        'player2_score' => 'integer',
    ];

    /**
     * The tournament
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Player 1
     */
    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    /**
     * Player 2
     */
    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    /**
     * Match winner
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Match loser
     */
    public function loser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loser_id');
    }

    /**
     * Next match (for bracket progression)
     */
    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'next_match_id');
    }

    /**
     * Scope for pending matches
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for a specific round
     */
    public function scopeRound($query, int $round)
    {
        return $query->where('round', $round);
    }

    /**
     * Check if match is ready to start
     */
    public function isReady(): bool
    {
        return $this->player1_id && $this->player2_id && $this->status === 'pending';
    }

    /**
     * Set winner
     */
    public function setWinner(int $winnerId): void
    {
        $loserId = $winnerId === $this->player1_id ? $this->player2_id : $this->player1_id;

        $this->update([
            'winner_id' => $winnerId,
            'loser_id' => $loserId,
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        // Advance winner to next match if exists
        if ($this->next_match_id) {
            $nextMatch = TournamentMatch::find($this->next_match_id);
            if ($nextMatch) {
                if (!$nextMatch->player1_id) {
                    $nextMatch->update(['player1_id' => $winnerId]);
                } else {
                    $nextMatch->update(['player2_id' => $winnerId]);
                }
            }
        }
    }
}
