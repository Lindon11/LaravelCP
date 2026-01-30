<?php

namespace App\Modules\Drugs;

use App\Modules\Module;
use App\Models\User;
use App\Models\Drug;

/**
 * Drugs Module
 * 
 * Handles drug trading with dynamic pricing
 * Classic gangster game mechanic with fluctuating market
 */
class DrugsModule extends Module
{
    protected string $name = 'Drugs';
    
    public function construct(): void
    {
        $this->config = [
            'max_carry' => 1000,
            'price_variance' => 0.30, // 30% variance
            'update_interval' => 300, // 5 minutes
        ];
    }
    
    /**
     * Get current drug prices
     */
    public function getDrugPrices(User $player): array
    {
        return Drug::all()
            ->map(function ($drug) use ($player) {
                $price = $this->calculatePrice($drug, $player);
                return $this->applyModuleHook('alterDrugPrice', [
                    'drug' => $drug,
                    'base_price' => $drug->base_price,
                    'current_price' => $price,
                    'location' => $player->location_id,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Calculate dynamic drug price
     */
    protected function calculatePrice(Drug $drug, User $player): int
    {
        $basePrice = $drug->base_price;
        $variance = $this->config['price_variance'];
        
        // Add randomness based on location and time
        $seed = $player->location_id . floor(time() / $this->config['update_interval']);
        mt_srand(crc32($seed . $drug->id));
        
        $multiplier = 1 + (mt_rand() / mt_getrandmax() * 2 - 1) * $variance;
        
        return (int) ($basePrice * $multiplier);
    }
    
    /**
     * Get player's drug inventory
     */
    public function getPlayerDrugs(User $player): array
    {
        return $player->drugs()
            ->with('drug')
            ->get()
            ->map(function ($playerDrug) {
                return $this->applyModuleHook('alterPlayerDrugData', [
                    'drug' => $playerDrug->drug,
                    'quantity' => $playerDrug->quantity,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Register module hooks
     */
    public function registerHooks(): void
    {
        // Will be called from hooks.php
    }
}
