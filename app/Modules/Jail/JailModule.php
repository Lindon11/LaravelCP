<?php

namespace App\Modules\Jail;

use App\Modules\Module;
use App\Models\User;
use App\Services\JailService;

/**
 * Jail Module
 * 
 * Handles jail system - jailed players, bust outs, and bail
 */
class JailModule extends Module
{
    protected string $name = 'Jail';
    
    protected JailService $jailService;
    
    public function construct(): void
    {
        $this->jailService = app(JailService::class);
        
        $this->config = [
            'bust_out_success_rate' => 0.5,
            'bail_cost_multiplier' => 1.5,
        ];
    }
    
    /**
     * Get jailed players
     */
    public function getJailedPlayers(User $user): array
    {
        $players = $this->jailService->getJailedPlayers($user);
        
        return $this->applyModuleHook('alterJailedPlayers', [
            'players' => $players,
            'user' => $user,
        ])['players'] ?? $players;
    }
    
    /**
     * Check if user is in jail
     */
    public function isInJail(User $user): bool
    {
        return $this->jailService->isInJail($user);
    }
    
    /**
     * Check if user is in super max
     */
    public function isInSuperMax(User $user): bool
    {
        return $this->jailService->isInSuperMax($user);
    }
    
    /**
     * Get remaining jail time
     */
    public function getRemainingTime(User $user): int
    {
        return $this->jailService->getRemainingTime($user);
    }
    
    /**
     * Attempt to bust a player out of jail
     */
    public function attemptBustOut(User $actor, User $target): array
    {
        // Apply hooks before bust out
        $this->applyModuleHook('beforeBustOut', [
            'actor' => $actor,
            'target' => $target,
        ]);
        
        $result = $this->jailService->attemptBustOut($actor, $target);
        
        // Apply hooks after bust out
        if ($result['success']) {
            $this->applyModuleHook('afterBustOut', [
                'actor' => $actor,
                'target' => $target,
                'result' => $result,
            ]);
        }
        
        return $result;
    }
    
    /**
     * Pay bail to get out of jail
     */
    public function bailOut(User $user): array
    {
        // Apply hooks before bail out
        $this->applyModuleHook('beforeBailOut', [
            'user' => $user,
        ]);
        
        $result = $this->jailService->bailOut($user);
        
        // Apply hooks after bail out
        if ($result['success']) {
            $this->applyModuleHook('afterBailOut', [
                'user' => $user,
                'cost' => $result['cost'] ?? 0,
            ]);
        }
        
        return $result;
    }
}
