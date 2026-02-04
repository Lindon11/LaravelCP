<?php

namespace App\Plugins\Tournament\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class Tournament extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'min_level',
        'max_level',
        'min_participants',
        'max_participants',
        'entry_fee',
        'prize_pool',
        'rewards',
        'rules',
        'registration_opens',
        'registration_closes',
        'starts_at',
        'ended_at',
        'winner_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'rewards' => 'array',
        'rules' => 'array',
        'metadata' => 'array',
        'registration_opens' => 'datetime',
        'registration_closes' => 'datetime',
        'starts_at' => 'datetime',
        'ended_at' => 'datetime',
        'entry_fee' => 'integer',
        'prize_pool' => 'integer',
        'min_participants' => 'integer',
        'max_participants' => 'integer',
    ];

    /**
     * Tournament participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    /**
     * Tournament matches
     */
    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    /**
     * Tournament winner
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * Tournament creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active tournaments
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registration', 'active']);
    }

    /**
     * Scope for registration open
     */
    public function scopeRegistrationOpen($query)
    {
        return $query->where('status', 'registration')
            ->where('registration_opens', '<=', now())
            ->where('registration_closes', '>', now());
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(): bool
    {
        if ($this->status !== 'registration') return false;
        if ($this->registration_opens && $this->registration_opens->isFuture()) return false;
        if ($this->registration_closes && $this->registration_closes->isPast()) return false;
        if ($this->max_participants && $this->participants()->count() >= $this->max_participants) return false;
        return true;
    }

    /**
     * Get current round
     */
    public function getCurrentRound(): int
    {
        return $this->matches()->max('round') ?? 0;
    }

    /**
     * Get remaining participants
     */
    public function getRemainingParticipants(): int
    {
        if ($this->type === 'battle_royale') {
            return $this->participants()->where('eliminated', false)->count();
        }

        $lastRound = $this->getCurrentRound();
        return $this->matches()
            ->where('round', $lastRound)
            ->whereNotNull('winner_id')
            ->count();
    }
}
