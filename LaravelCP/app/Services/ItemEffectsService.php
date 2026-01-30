<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserEquipment;

class ItemEffectsService
{
    /**
     * Get all stat bonuses from equipped items.
     */
    public function getEquipmentBonuses(User $user): array
    {
        $equipment = UserEquipment::where('user_id', $user->id)
            ->with('item')
            ->get();

        $bonuses = [
            'strength' => 0,
            'defense' => 0,
            'speed' => 0,
            'damage' => 0,
            'health' => 0,
            'accuracy' => 0,
            'evasion' => 0,
        ];

        foreach ($equipment as $equip) {
            if (!$equip->isEquipped()) {
                continue;
            }

            $itemStats = $equip->getStatBonuses();
            
            foreach ($itemStats as $stat => $value) {
                if (isset($bonuses[$stat])) {
                    $bonuses[$stat] += $value;
                }
            }
        }

        return $bonuses;
    }

    /**
     * Apply equipment bonuses to base stats.
     */
    public function applyBonuses(User $user, array $baseStats): array
    {
        $bonuses = $this->getEquipmentBonuses($user);
        $modifiedStats = $baseStats;

        foreach ($bonuses as $stat => $bonus) {
            if (isset($modifiedStats[$stat])) {
                $modifiedStats[$stat] += $bonus;
            }
        }

        return $modifiedStats;
    }

    /**
     * Get total strength with equipment.
     */
    public function getTotalStrength(User $user): int
    {
        $bonuses = $this->getEquipmentBonuses($user);
        $baseStrength = $user->strength ?? $user->getAttribute('strength') ?? 0;
        return $baseStrength + $bonuses['strength'];
    }

    /**
     * Get total defense with equipment.
     */
    public function getTotalDefense(User $user): int
    {
        $bonuses = $this->getEquipmentBonuses($user);
        $baseDefense = $user->defense ?? $user->getAttribute('defense') ?? 0;
        return $baseDefense + $bonuses['defense'];
    }

    /**
     * Get total speed with equipment.
     */
    public function getTotalSpeed(User $user): int
    {
        $bonuses = $this->getEquipmentBonuses($user);
        return ($user->speed ?? 0) + $bonuses['speed'];
    }

    /**
     * Get attack damage with equipment.
     */
    public function getAttackDamage(User $user): int
    {
        $baseDamage = $this->getTotalStrength($user);
        $bonuses = $this->getEquipmentBonuses($user);
        
        return $baseDamage + $bonuses['damage'];
    }

    /**
     * Get total health with equipment.
     */
    public function getTotalHealth(User $user): int
    {
        $baseHealth = $user->health ?? 100;
        $bonuses = $this->getEquipmentBonuses($user);
        
        return $baseHealth + $bonuses['health'];
    }

    /**
     * Get accuracy with equipment.
     */
    public function getAccuracy(User $user): float
    {
        $bonuses = $this->getEquipmentBonuses($user);
        $baseAccuracy = 0.75; // 75% base accuracy
        
        return min(0.95, $baseAccuracy + ($bonuses['accuracy'] / 100));
    }

    /**
     * Get evasion with equipment.
     */
    public function getEvasion(User $user): float
    {
        $bonuses = $this->getEquipmentBonuses($user);
        $baseEvasion = 0.10; // 10% base evasion
        
        return min(0.50, $baseEvasion + ($bonuses['evasion'] / 100));
    }

    /**
     * Get formatted equipment summary.
     */
    public function getEquipmentSummary(User $user): array
    {
        $equipment = UserEquipment::where('user_id', $user->id)
            ->with('item')
            ->get();

        $summary = [];
        
        foreach ($equipment as $equip) {
            $summary[$equip->slot] = [
                'equipped' => $equip->isEquipped(),
                'item' => $equip->item ? [
                    'id' => $equip->item->id,
                    'name' => $equip->item->name,
                    'type' => $equip->item->type,
                    'rarity' => $equip->item->rarity,
                    'stats' => $equip->item->stats,
                ] : null,
                'bonuses' => $equip->getStatBonuses(),
            ];
        }

        return $summary;
    }

    /**
     * Equip an item to a slot.
     */
    public function equipItem(User $user, int $itemId, string $slot): bool
    {
        // Get or create equipment slot
        $equipment = UserEquipment::firstOrCreate(
            ['user_id' => $user->id, 'slot' => $slot],
            ['item_id' => null]
        );

        // Check if user has the item in inventory
        $inventory = $user->inventory()->where('item_id', $itemId)->first();
        
        if (!$inventory || $inventory->quantity < 1) {
            return false;
        }

        // Equip the item
        $equipment->update(['item_id' => $itemId]);
        
        return true;
    }

    /**
     * Unequip an item from a slot.
     */
    public function unequipItem(User $user, string $slot): bool
    {
        $equipment = UserEquipment::where('user_id', $user->id)
            ->where('slot', $slot)
            ->first();

        if (!$equipment) {
            return false;
        }

        $equipment->update(['item_id' => null]);
        
        return true;
    }

    /**
     * Initialize equipment slots for a new user.
     */
    public function initializeEquipmentSlots(User $user): void
    {
        $slots = ['weapon', 'armor', 'helmet', 'gloves', 'boots', 'accessory', 'vehicle'];
        
        foreach ($slots as $slot) {
            UserEquipment::firstOrCreate(
                ['user_id' => $user->id, 'slot' => $slot],
                ['item_id' => null]
            );
        }
    }
}
