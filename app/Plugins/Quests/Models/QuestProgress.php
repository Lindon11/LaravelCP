<?php

namespace App\Plugins\Quests\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestProgress extends Model
{
    protected $table = 'quest_progress';

    protected $fillable = [
        'user_id',
        'quest_id',
        'status',
        'started_at',
        'completed_at',
        'rewards_claimed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'rewards_claimed' => 'boolean',
    ];

    /**
     * The user working on the quest
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The quest being progressed
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    /**
     * Objective progress entries
     */
    public function objectiveProgress(): HasMany
    {
        return $this->hasMany(QuestObjectiveProgress::class);
    }
}
