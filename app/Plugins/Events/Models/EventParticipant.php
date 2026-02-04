<?php

namespace App\Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class EventParticipant extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'joined_at',
        'score',
        'rank',
        'reward_claimed',
        'reward_data',
        'metadata',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'reward_claimed' => 'boolean',
        'reward_data' => 'array',
        'metadata' => 'array',
        'score' => 'integer',
        'rank' => 'integer',
    ];

    /**
     * The event
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The participant user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unclaimed rewards
     */
    public function scopeUnclaimedRewards($query)
    {
        return $query->where('reward_claimed', false)
            ->whereNotNull('rank');
    }
}
