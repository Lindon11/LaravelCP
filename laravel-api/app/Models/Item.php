<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'image',
        'price',
        'sell_price',
        'tradeable',
        'stackable',
        'max_stack',
        'stats',
        'requirements',
        'rarity',
    ];

    protected $casts = [
        'tradeable' => 'boolean',
        'stackable' => 'boolean',
        'stats' => 'array',
        'requirements' => 'array',
    ];

    public function inventories()
    {
        return $this->hasMany(PlayerInventory::class);
    }

    public function canBeUsedBy(User $player): bool
    {
        if (!$this->requirements) {
            return true;
        }

        if (isset($this->requirements['level']) && $player->level < $this->requirements['level']) {
            return false;
        }

        return true;
    }

    public function getRarityColorAttribute(): string
    {
        return match($this->rarity) {
            'legendary' => 'text-orange-500',
            'epic' => 'text-purple-500',
            'rare' => 'text-blue-500',
            'uncommon' => 'text-green-500',
            default => 'text-gray-500',
        };
    }
}
