<?php

namespace App\Plugins\Quests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Core\Models\User;

class PlayerQuest extends Model
{
    protected $fillable = [
        'user_id',
        'quest_id',
        'status',
        'progress',
        'started_at',
        'completed_at',
        'expires_at',
        'reward_claimed',
    ];

    protected $casts = [
        'progress' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'reward_claimed' => 'boolean',
    ];

    /**
     * The user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The quest
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Quest objectives progress
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(QuestObjective::class);
    }

    /**
     * Scope for active quests
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'in_progress']);
    }

    /**
     * Scope for completed quests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if quest is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if all objectives are complete
     */
    public function areObjectivesComplete(): bool
    {
        $quest = $this->quest;
        if (!$quest || empty($quest->objectives)) {
            return true;
        }

        foreach ($quest->objectives as $index => $objective) {
            $progress = $this->progress[$index] ?? 0;
            $required = $objective['count'] ?? 1;

            if ($progress < $required) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update objective progress
     */
    public function updateObjective(int $index, int $amount = 1): void
    {
        $progress = $this->progress ?? [];
        $progress[$index] = ($progress[$index] ?? 0) + $amount;
        $this->update(['progress' => $progress]);

        if ($this->areObjectivesComplete()) {
            $this->update(['status' => 'ready_to_complete']);
        }
    }
}
