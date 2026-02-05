<?php

namespace App\Plugins\Quests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjectiveProgress extends Model
{
    protected $table = 'quest_objective_progress';

    protected $fillable = [
        'quest_progress_id',
        'objective_id',
        'current_count',
        'target_count',
        'completed',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'target_count' => 'integer',
        'completed' => 'boolean',
    ];

    /**
     * The parent quest progress
     */
    public function questProgress(): BelongsTo
    {
        return $this->belongsTo(QuestProgress::class);
    }

    /**
     * The quest objective
     */
    public function objective(): BelongsTo
    {
        return $this->belongsTo(QuestObjective::class, 'objective_id');
    }
}
