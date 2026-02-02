<?php

namespace App\Modules\Theft;

use App\Modules\Module;
use App\Models\User;
use App\Models\TheftType;
use App\Models\Garage;
use App\Services\TheftService;

/**
 * Theft Module
 * 
 * Handles car theft system - allows players to steal cars and manage garage
 */
class TheftModule extends Module
{
    protected string $name = 'Theft';
    
    protected TheftService $theftService;
    
    public function construct(): void
    {
        $this->theftService = app(TheftService::class);
        
        $this->config = [
            'cooldown' => 300, // 5 minutes
            'max_garage_size' => 10,
        ];
    }
    
    /**
     * Get available theft types for current user
     */
    public function getAvailableThefts(User $user): array
    {
        return TheftType::where('required_level', '<=', $user->level)
            ->orderBy('success_rate', 'desc')
            ->get()
            ->map(function ($type) use ($user) {
                return $this->applyModuleHook('alterTheftData', [
                    'theft_type' => $type,
                    'user' => $user,
                ]);
            })
            ->toArray();
    }
    
    /**
     * Check if user can attempt theft
     */
    public function canAttemptTheft(User $user): bool
    {
        return $this->theftService->canAttemptTheft($user);
    }
    
    /**
     * Get remaining cooldown time
     */
    public function getRemainingCooldown(User $user): int
    {
        return $this->theftService->getRemainingCooldown($user);
    }
    
    /**
     * Attempt to steal a car
     */
    public function attemptTheft(User $user, TheftType $theftType): array
    {
        // Apply hooks before theft
        $this->applyModuleHook('beforeTheftAttempt', [
            'user' => $user,
            'theft_type' => $theftType,
        ]);
        
        $result = $this->theftService->attemptTheft($user, $theftType);
        
        // Apply hooks after theft
        $this->applyModuleHook('afterTheftAttempt', [
            'user' => $user,
            'theft_type' => $theftType,
            'result' => $result,
        ]);
        
        return $result;
    }
    
    /**
     * Get user's garage
     */
    public function getGarage(User $user): array
    {
        return $this->theftService->getGarage($user);
    }
    
    /**
     * Sell a car from garage
     */
    public function sellCar(User $user, Garage $garage): int
    {
        // Apply hooks before selling
        $this->applyModuleHook('beforeCarSell', [
            'user' => $user,
            'garage' => $garage,
        ]);
        
        $value = $this->theftService->sellCar($user, $garage);
        
        // Apply hooks after selling
        $this->applyModuleHook('afterCarSell', [
            'user' => $user,
            'value' => $value,
        ]);
        
        return $value;
    }
}
