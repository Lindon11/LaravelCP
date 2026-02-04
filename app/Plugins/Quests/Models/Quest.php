<?php

namespace App\Plugins\Quests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quest extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'story_text',
        'category',
        'min_level',
        'max_level',
        'prerequisite_quest_id',
        'rewards',
        'objectives',
        'time_limit',
        'repeatable',
        'repeat_cooldown',
        'enabled',
        'sort_order',
        'npc_giver',
        'npc_completer',
        'location_id',
        'metadata',
    ];

    protected $casts = [
        'rewards' => 'array',
        'objectives' => 'array',
        'metadata' => 'array',
        'enabled' => 'boolean',
        'repeatable' => 'boolean',
        'time_limit' => 'integer',
        'repeat_cooldown' => 'integer',
        'min_level' => 'integer',
        'max_level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Prerequisite quest
     */
    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'prerequisite_quest_id');
    }

    /**
     * Quests that require this one
     */
    public function followUpQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'prerequisite_quest_id');
    }

    /**
     * Player quests
     */
    public function playerQuests(): HasMany
    {
        return $this->hasMany(PlayerQuest::class);
    }

    /**
     * Scope for enabled quests
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if quest is available for user level
     */
    public function isAvailableForLevel(int $level): bool
    {
        if ($this->min_level && $level < $this->min_level) {
            return false;
        }

        if ($this->max_level && $level > $this->max_level) {
            return false;
        }

        return true;
    }
}
