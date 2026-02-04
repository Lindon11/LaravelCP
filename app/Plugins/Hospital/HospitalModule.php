<?php

namespace App\Plugins\Hospital;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Hospital\Services\HospitalService;

/**
 * Hospital Module
 * 
 * Handles healing system - allows players to restore health
 */
class HospitalModule extends Plugin
{
    protected string $name = 'Hospital';
    
    protected HospitalService $hospitalService;
    
    public function construct(): void
    {
        $this->hospitalService = app(HospitalService::class);
        
        $this->config = [
            'cost_per_hp' => HospitalService::COST_PER_HP,
        ];
    }
    
    /**
     * Calculate full heal cost
     */
    public function calculateFullHealCost(User $user): int
    {
        $cost = $this->hospitalService->calculateFullHealCost($user);
        
        $result = $this->applyModuleHook('alterHealCost', [
            'user' => $user,
            'cost' => $cost,
        ]);
        
        return $result['cost'] ?? $cost;
    }
    
    /**
     * Heal by specific amount
     */
    public function heal(User $user, int $amount): array
    {
        // Apply hooks before healing
        $this->applyModuleHook('beforeHeal', [
            'user' => $user,
            'amount' => $amount,
        ]);
        
        $result = $this->hospitalService->heal($user, $amount);
        
        // Apply hooks after healing
        if ($result['success']) {
            $this->applyModuleHook('afterHeal', [
                'user' => $user,
                'amount' => $amount,
                'cost' => $result['cost'] ?? 0,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Heal to full health
     */
    public function healFull(User $user): array
    {
        // Apply hooks before healing
        $this->applyModuleHook('beforeHealFull', [
            'user' => $user,
        ]);
        
        $result = $this->hospitalService->healFull($user);
        
        // Apply hooks after healing
        if ($result['success']) {
            $this->applyModuleHook('afterHealFull', [
                'user' => $user,
                'cost' => $result['cost'] ?? 0,
            ]);
        }
        
        return $result;
    }
}
