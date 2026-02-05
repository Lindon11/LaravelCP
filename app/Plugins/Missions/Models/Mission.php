<?php

namespace App\Plugins\Missions\Models;

use App\Core\Models\Item;
use App\Core\Models\Location;
use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'required_level',
        'required_location_id',
        'objective_type',
        'objective_count',
        'objective_data',
        'cash_reward',
        'respect_reward',
        'experience_reward',
        'item_reward_id',
        'item_reward_quantity',
        'cooldown_hours',
        'order',
        'is_active',
    ];

    protected $casts = [
        'objective_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'required_location_id');
    }

    public function itemReward(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_reward_id');
    }

    public function playerMissions(): HasMany
    {
        return $this->hasMany(PlayerMission::class);
    }

    public function isAvailableFor(User $player): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($player->level < $this->required_level) {
            return false;
        }

        if ($this->required_location_id && $player->location_id !== $this->required_location_id) {
            return false;
        }

        return true;
    }
}
