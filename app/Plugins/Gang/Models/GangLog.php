<?php

namespace App\Plugins\Gang\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GangLog extends Model
{
    const UPDATED_AT = null; // Only track created_at

    protected $fillable = [
        'gang_id',
        'user_id',
        'target_player_id',
        'action',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the gang that owns the log.
     */
    public function gang(): BelongsTo
    {
        return $this->belongsTo(Gang::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the target player (if applicable).
     */
    public function targetPlayer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_player_id');
    }

    /**
     * Scope to filter by action type.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get recent logs.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Get formatted log message.
     */
    public function getFormattedMessage(): string
    {
        $playerName = $this->user ? $this->user->username : 'Unknown';
        $targetName = $this->targetPlayer ? $this->targetPlayer->username : null;

        return match($this->action) {
            'joined' => "{$playerName} joined the gang",
            'left' => "{$playerName} left the gang",
            'kicked' => "{$playerName} kicked {$targetName} from the gang",
            'promoted' => "{$playerName} promoted {$targetName}",
            'demoted' => "{$playerName} demoted {$targetName}",
            'deposit' => "{$playerName} deposited into gang bank",
            'withdraw' => "{$playerName} withdrew from gang bank",
            'invite_sent' => "{$playerName} invited {$targetName} to the gang",
            'invite_accepted' => "{$targetName} accepted gang invitation",
            'invite_rejected' => "{$targetName} rejected gang invitation",
            'war_declared' => "{$playerName} declared war",
            'war_ended' => "{$playerName} ended a war",
            'property_purchased' => "{$playerName} purchased a property",
            'property_sold' => "{$playerName} sold a property",
            'mass_message' => "{$playerName} sent a mass message",
            default => "{$playerName} performed action: {$this->action}",
        };
    }

    /**
     * Alias for getFormattedMessage.
     */
    public function getFormattedAction(): string
    {
        return $this->getFormattedMessage();
    }
}
