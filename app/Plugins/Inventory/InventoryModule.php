<?php

namespace App\Plugins\Inventory;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Core\Models\Item;

/**
 * Inventory Module
 * 
 * Handles player inventory, items, equipment, and consumables
 * Core system used by many other modules
 */
class InventoryModule extends Plugin
{
    protected string $name = 'Inventory';
    
    public function construct(): void
    {
        $this->config = [
            'max_inventory_size' => 100,
            'sell_percentage' => 0.50, // 50% return on sale
            'stack_limit' => 999,
        ];
    }
    
    /**
     * Get player's inventory
     */
    public function getPlayerInventory(User $player): array
    {
        return $player->inventory()
            ->with('item')
            ->get()
            ->map(function ($inventoryItem) {
                return $this->applyModuleHook('alterInventoryItemData', [
                    'id' => $inventoryItem->id,
                    'item' => $inventoryItem->item,
                    'quantity' => $inventoryItem->quantity,
                    'equipped' => $inventoryItem->equipped,
                    'acquired_at' => $inventoryItem->created_at,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Get equipped items
     */
    public function getEquippedItems(User $player): array
    {
        return $player->inventory()
            ->with('item')
            ->where('equipped', true)
            ->get()
            ->map(function ($inventoryItem) {
                return $this->applyModuleHook('alterEquippedItemData', [
                    'item' => $inventoryItem->item,
                    'slot' => $inventoryItem->item->slot,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Calculate total inventory value
     */
    public function calculateInventoryValue(User $player): int
    {
        $totalValue = 0;
        
        foreach ($player->inventory as $inventoryItem) {
            $itemValue = $inventoryItem->item->price * $inventoryItem->quantity;
            $totalValue += $itemValue;
        }
        
        return (int) $this->applyModuleHook('alterInventoryValue', $totalValue);
    }
    
    /**
     * Get item bonuses from equipped items
     */
    public function getEquipmentBonuses(User $player): array
    {
        $bonuses = [
            'attack' => 0,
            'defense' => 0,
            'speed' => 0,
            'health' => 0,
        ];
        
        $equipped = $player->inventory()->where('equipped', true)->with('item')->get();
        
        foreach ($equipped as $inventoryItem) {
            $item = $inventoryItem->item;
            $bonuses['attack'] += $item->attack_bonus ?? 0;
            $bonuses['defense'] += $item->defense_bonus ?? 0;
            $bonuses['speed'] += $item->speed_bonus ?? 0;
            $bonuses['health'] += $item->health_bonus ?? 0;
        }
        
        return $this->applyModuleHook('alterEquipmentBonuses', $bonuses);
    }
    
    /**
     * Register module hooks
     */
    public function registerHooks(): void
    {
        // Will be called from hooks.php
    }
}
