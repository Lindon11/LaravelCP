<?php

namespace App\Plugins\Properties;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Properties\Models\Property;

/**
 * Properties Module
 * 
 * Handles property ownership and income generation
 * Players can buy/sell properties for passive income
 */
class PropertiesModule extends Plugin
{
    protected string $name = 'Properties';
    
    public function construct(): void
    {
        $this->config = [
            'max_properties' => 10,
            'income_interval' => 3600, // 1 hour
            'sell_percentage' => 0.80, // 80% return on sale
        ];
    }
    
    /**
     * Get available properties for purchase
     */
    public function getAvailableProperties(): array
    {
        return Property::all()
            ->map(function ($property) {
                return $this->applyModuleHook('alterPropertyData', [
                    'property' => $property,
                    'price' => $property->price,
                    'income_rate' => $property->income_rate,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Get player's owned properties
     */
    public function getMyProperties(User $player): array
    {
        return $player->properties()
            ->with('property')
            ->get()
            ->map(function ($ownership) {
                return $this->applyModuleHook('alterOwnedPropertyData', [
                    'ownership' => $ownership,
                    'property' => $ownership->property,
                    'purchase_date' => $ownership->purchased_at,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Calculate total income available
     */
    public function calculateIncome(User $player): int
    {
        $totalIncome = 0;
        $properties = $player->properties;
        
        foreach ($properties as $ownership) {
            $hoursSincePurchase = $ownership->purchased_at->diffInHours(now());
            $income = $ownership->property->income_rate * $hoursSincePurchase;
            $totalIncome += $income;
        }
        
        return (int) $this->applyModuleHook('alterTotalIncome', $totalIncome);
    }
    
    /**
     * Register module hooks
     */
    public function registerHooks(): void
    {
        // Will be called from hooks.php
    }
}
