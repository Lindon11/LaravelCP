<?php

namespace App\Plugins\Travel;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Core\Models\Location;

/**
 * Travel Module
 * 
 * Handles player movement between different game locations
 * Each location may have different prices, activities, etc.
 */
class TravelModule extends Plugin
{
    protected string $name = 'Travel';
    
    public function construct(): void
    {
        $this->config = [
            'travel_cooldown' => 30, // 30 seconds
            'travel_cost_multiplier' => 100, // Per distance unit
        ];
    }
    
    /**
     * Get available locations
     */
    public function getAvailableLocations(): array
    {
        return Location::all()
            ->map(function ($location) {
                return $this->applyModuleHook('alterLocationData', [
                    'location' => $location,
                    'name' => $location->name,
                    'description' => $location->description,
                    'level_required' => $location->level_required,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Get players in specific location
     */
    public function getPlayersInLocation(Location $location): array
    {
        return User::where('location_id', $location->id)
            ->where('last_activity', '>', now()->subMinutes(15))
            ->limit(50)
            ->get()
            ->map(function ($player) {
                return $this->applyModuleHook('alterPlayerInLocationData', [
                    'user_id' => $player->id,
                    'username' => $player->username,
                    'level' => $player->level,
                    'rank' => $player->rank,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Calculate travel cost
     */
    public function calculateTravelCost(Location $from, Location $to): int
    {
        // Simple distance calculation (can be enhanced)
        $distance = abs($from->id - $to->id);
        $cost = $distance * $this->config['travel_cost_multiplier'];
        
        return (int) $this->applyModuleHook('alterTravelCost', $cost);
    }
    
    /**
     * Register module hooks
     */
    public function registerHooks(): void
    {
        // Will be called from hooks.php
    }
}
