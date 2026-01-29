<?php

namespace App\Modules\Crimes;

use App\Modules\Module;
use App\Models\User;
use App\Models\Crime;

/**
 * Crimes Module
 * 
 * Handles crime system - similar to V2 crimes module
 * Allows players to commit crimes for cash and experience
 */
class CrimesModule extends Module
{
    protected string $name = 'Crimes';
    
    public function construct(): void
    {
        $this->config = [
            'cooldown' => 60, // seconds
            'energy_cost' => 10,
            'jail_chance' => 0.3,
        ];
    }
    
    /**
     * Get available crimes for current user
     */
    public function getAvailableCrimes(User $user): array
    {
        return Crime::where('active', true)
            ->where('required_level', '<=', $user->level)
            ->orderBy('difficulty')
            ->get()
            ->map(function ($crime) use ($user) {
                $successRate = $this->calculateSuccessRate($crime, $user);
                
                // Apply hook and merge with crime data
                $hookData = $this->applyModuleHook('alterCrimeData', [
                    'crime' => $crime,
                    'user' => $user,
                    'success_rate' => $successRate,
                ]);
                
                // Return crime attributes with success rate
                return array_merge($crime->toArray(), [
                    'success_rate' => $successRate,
                ], $hookData);
            })
            ->toArray();
    }
    
    /**
     * Attempt to commit a crime
     */
    public function attemptCrime(User $user, Crime $crime): array
    {
        // Check cooldown
        if ($user->hasTimer('crime')) {
            return ['success' => false, 'message' => 'You must wait before committing another crime'];
        }
        
        // Check energy
        if ($user->energy < $crime->energy_cost) {
            return ['success' => false, 'message' => 'Not enough energy'];
        }
        
        // Check level requirement
        if ($user->level < $crime->required_level) {
            return ['success' => false, 'message' => 'Level requirement not met'];
        }
        
        // Apply hooks before crime
        $this->applyModuleHook('beforeCrimeAttempt', [
            'user' => $user,
            'crime' => $crime,
        ]);
        
        // Calculate success
        $successRate = $this->calculateSuccessRate($crime, $user);
        $success = (rand(1, 100) / 100) <= $successRate;
        
        $result = [];
        
        if ($success) {
            $cashEarned = rand($crime->min_cash, $crime->max_cash);
            $expEarned = $crime->experience_reward;
            
            $user->cash += $cashEarned;
            $user->experience += $expEarned;
            $user->energy -= $crime->energy_cost;
            
            $result = [
                'success' => true,
                'message' => "Crime successful! Earned {$this->money($cashEarned)} and {$expEarned} EXP",
                'cash_earned' => $cashEarned,
                'exp_earned' => $expEarned,
            ];
        } else {
            // Check if caught
            if ((rand(1, 100) / 100) <= $this->config['jail_chance']) {
                $jailTime = rand(60, 300); // 1-5 minutes
                $user->jail_until = now()->addSeconds($jailTime);
                
                $result = [
                    'success' => false,
                    'message' => "Crime failed! You were caught and sent to jail for {$jailTime} seconds",
                    'jailed' => true,
                    'jail_time' => $jailTime,
                ];
            } else {
                $user->energy -= $crime->energy_cost;
                $result = [
                    'success' => false,
                    'message' => 'Crime failed! You got away but earned nothing',
                ];
            }
        }
        
        // Set cooldown
        $user->setTimer('crime', $this->config['cooldown']);
        $user->save();
        
        // Track achievement progress
        $totalCrimes = \App\Models\CrimeAttempt::where('user_id', $user->id)->count();
        $achievementService = app(\App\Services\AchievementService::class);
        $earnedAchievements = $achievementService->checkProgress($user, 'crime_count', $totalCrimes);
        
        // Check level achievements
        $levelAchievements = $achievementService->checkProgress($user, 'level_reached', $user->level);
        $earnedAchievements = array_merge($earnedAchievements, $levelAchievements);
        
        // Add achievement notifications to result
        if (!empty($earnedAchievements)) {
            $result['achievements'] = $earnedAchievements;
        }
        
        // Track action
        $this->trackAction('crime_attempt', [
            'crime_id' => $crime->id,
            'success' => $success,
            'cash_earned' => $result['cash_earned'] ?? 0,
        ]);
        
        // Fire hook after crime
        $this->applyModuleHook('afterCrimeAttempt', [
            'user' => $user,
            'crime' => $crime,
            'success' => $success,
            'result' => $result,
        ]);
        
        return $result;
    }
    
    /**
     * Calculate success rate based on user stats
     */
    protected function calculateSuccessRate(Crime $crime, User $user): float
    {
        $baseRate = $crime->success_rate / 100;
        
        // Adjust based on user level
        $levelBonus = ($user->level - $crime->required_level) * 0.02;
        
        // Adjust based on user stats
        $statBonus = ($user->strength + $user->speed) / 2000;
        
        $finalRate = $baseRate + $levelBonus + $statBonus;
        
        // Apply module hooks to modify rate
        $finalRate = $this->applyModuleHook('modifyCrimeSuccessRate', [
            'base_rate' => $baseRate,
            'final_rate' => $finalRate,
            'user' => $user,
            'crime' => $crime,
        ])['final_rate'] ?? $finalRate;
        
        return min(0.95, max(0.05, $finalRate)); // Cap between 5% and 95%
    }
    
    /**
     * Get crime statistics for user
     */
    public function getStats(User $user): array
    {
        return [
            'total_crimes' => $user->crime_attempts()->count(),
            'successful_crimes' => $user->crime_attempts()->where('success', true)->count(),
            'total_earned' => $user->crime_attempts()->sum('cash_earned'),
            'times_jailed' => $user->crime_attempts()->where('jailed', true)->count(),
        ];
    }
}
