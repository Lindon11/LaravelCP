<?php

namespace App\Plugins\Quests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestObjective extends Model
{
    protected $fillable = [
        'quest_id',
        'type',
        'description',
        'target_count',
        'sort_order',
    ];

    protected $casts = [
        'target_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * The quest this objective belongs to
     */
    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
