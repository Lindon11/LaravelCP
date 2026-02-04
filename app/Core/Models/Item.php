<?php

namespace App\Core\Models;

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
        'effects',
        'cooldown',
        'duration',
        'is_usable',
        'requirements',
        'rarity',
    ];

    protected $casts = [
        'tradeable' => 'boolean',
        'stackable' => 'boolean',
        'is_usable' => 'boolean',
        'stats' => 'array',
        'effects' => 'array',
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

    /**
     * Apply item effects to a player
     */
    public function applyEffects(User $player): array
    {
        if (!$this->is_usable || !$this->effects) {
            return ['success' => false, 'message' => 'This item cannot be used'];
        }

        $results = [];

        foreach ($this->effects as $effect) {
            $effectName = $effect['name'] ?? null;
            $value = $effect['value'] ?? 0;
            $modifierType = $effect['modifier_type'] ?? 'flat';

            if (!$effectName) continue;

            switch ($effectName) {
                case 'heal':
                    $healed = $modifierType === 'percent' 
                        ? ($player->max_health * $value / 100) 
                        : $value;
                    $player->health = min($player->max_health, $player->health + $healed);
                    $player->save();
                    $results[] = "Restored {$healed} health";
                    break;

                case 'restore_energy':
                    $restored = $modifierType === 'percent' 
                        ? ($player->max_energy * $value / 100) 
                        : $value;
                    $player->energy = min($player->max_energy, $player->energy + $restored);
                    $player->save();
                    $results[] = "Restored {$restored} energy";
                    break;

                case 'boost_strength':
                case 'boost_defense':
                case 'boost_speed':
                case 'boost_damage':
                case 'reduce_cooldown':
                case 'experience_boost':
                case 'money_boost':
                case 'crime_success':
                    // These are timed buffs - create active effect
                    $stat = str_replace('boost_', '', $effectName);
                    $stat = str_replace('_boost', '', $stat);
                    
                    PlayerActiveEffect::create([
                        'user_id' => $player->id,
                        'item_id' => $this->id,
                        'effect_name' => $effectName,
                        'stat' => $stat,
                        'value' => $value,
                        'modifier_type' => $modifierType,
                        'expires_at' => now()->addSeconds($this->duration),
                    ]);
                    $results[] = "Applied {$effectName} buff for " . ($this->duration / 60) . " minutes";
                    break;

                case 'jail_reduction':
                    if ($player->jail_time > 0) {
                        $reduction = $modifierType === 'percent' 
                            ? ($player->jail_time * $value / 100) 
                            : $value;
                        $player->jail_time = max(0, $player->jail_time - $reduction);
                        $player->save();
                        $results[] = "Reduced jail time by {$reduction} seconds";
                    }
                    break;

                case 'revive':
                    if ($player->hospital_time > 0) {
                        $player->hospital_time = 0;
                        $player->health = max(1, $player->max_health * 0.25); // Revive with 25% HP
                        $player->save();
                        $results[] = "Revived from hospital";
                    }
                    break;
            }
        }

        return ['success' => true, 'results' => $results];
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

    public function hasEffect(string $effectName): bool
    {
        if (!$this->effects) return false;
        
        return collect($this->effects)->contains('name', $effectName);
    }

    public function getEffectValue(string $effectName): ?float
    {
        if (!$this->effects) return null;
        
        $effect = collect($this->effects)->firstWhere('name', $effectName);
        return $effect['value'] ?? null;
    }
}
