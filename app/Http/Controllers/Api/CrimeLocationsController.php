<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CrimeLocation;
use App\Models\CrimeLocationAttempt;
use Illuminate\Http\Request;

class CrimeLocationsController extends Controller
{
    /**
     * Get all available crime locations
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $locations = CrimeLocation::active()
            ->ordered()
            ->get()
            ->map(function ($location) use ($user) {
                $stats = $location->getUserStats($user);
                
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'description' => $location->description,
                    'energy_cost' => $location->energy_cost,
                    'min_level' => $location->min_level,
                    'difficulty' => $location->difficulty,
                    'color_theme' => $location->color_theme,
                    'is_locked' => $user->level < $location->min_level,
                    'stats' => $stats,
                ];
            });
        
        $timer = $user->getTimer('crime');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'locations' => $locations,
            'cooldown' => $cooldown,
            'player' => [
                'level' => $user->level,
                'energy' => $user->energy,
                'cash' => $user->cash,
            ],
        ]);
    }
    
    /**
     * Get details for a specific crime location
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $location = CrimeLocation::findOrFail($id);
        
        // Check if user has sufficient level
        if ($user->level < $location->min_level) {
            return response()->json([
                'success' => false,
                'message' => "You need to be level {$location->min_level} to access this location.",
            ], 403);
        }
        
        $stats = $location->getUserStats($user);
        $successRate = $location->calculateSuccessRate($user);
        
        // Get recent attempts (last 10)
        $recentAttempts = $location->userAttempts($user)
            ->orderBy('attempted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'success' => $attempt->success,
                    'cash_earned' => $attempt->cash_earned,
                    'exp_earned' => $attempt->exp_earned,
                    'jailed' => $attempt->jailed,
                    'attempted_at' => $attempt->attempted_at->diffForHumans(),
                ];
            });
        
        $timer = $user->getTimer('crime');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'description' => $location->description,
                'energy_cost' => $location->energy_cost,
                'min_level' => $location->min_level,
                'success_rate' => $successRate,
                'difficulty' => $location->difficulty,
                'reward_range' => [
                    'cash' => [$location->min_cash, $location->max_cash],
                    'exp' => [$location->min_exp, $location->max_exp],
                ],
                'jail_chance' => $location->jail_chance,
            ],
            'stats' => $stats,
            'recent_attempts' => $recentAttempts,
            'cooldown' => $cooldown,
            'player' => [
                'energy' => $user->energy,
                'cash' => $user->cash,
                'level' => $user->level,
            ],
        ]);
    }
    
    /**
     * Attempt a crime at a location
     */
    public function attempt(Request $request, int $id)
    {
        $request->validate([
            'action_count' => 'sometimes|integer|min:1|max:100',
        ]);
        
        $user = $request->user();
        $location = CrimeLocation::findOrFail($id);
        $actionCount = $request->input('action_count', 1);
        
        // Validate level requirement
        if ($user->level < $location->min_level) {
            return response()->json([
                'success' => false,
                'message' => "You need to be level {$location->min_level} to commit crimes here.",
            ], 403);
        }
        
        // Check energy
        $totalEnergy = $location->energy_cost * $actionCount;
        if ($user->energy < $totalEnergy) {
            return response()->json([
                'success' => false,
                'message' => "You don't have enough energy. Need {$totalEnergy}, have {$user->energy}.",
            ], 400);
        }
        
        // Check cooldown
        $timer = $user->getTimer('crime');
        if ($timer && now()->lt($timer->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'You must wait before attempting another crime.',
                'cooldown' => max(0, now()->diffInSeconds($timer->expires_at, false)),
            ], 429);
        }
        
        // Perform the crime(s)
        $results = [];
        $totalCash = 0;
        $totalExp = 0;
        $totalRespect = 0;
        $successCount = 0;
        $failCount = 0;
        $jailed = false;
        
        for ($i = 0; $i < $actionCount; $i++) {
            $successRate = $location->calculateSuccessRate($user);
            $isSuccess = rand(1, 100) <= $successRate;
            
            $cashEarned = 0;
            $expEarned = 0;
            $respectEarned = 0;
            
            if ($isSuccess) {
                $successCount++;
                $cashEarned = rand($location->min_cash, $location->max_cash);
                $expEarned = rand($location->min_exp, $location->max_exp);
                $respectEarned = $location->respect_reward;
                
                $totalCash += $cashEarned;
                $totalExp += $expEarned;
                $totalRespect += $respectEarned;
            } else {
                $failCount++;
                // Check if jailed
                if (rand(1, 100) <= $location->jail_chance) {
                    $jailed = true;
                }
            }
            
            // Log attempt
            CrimeLocationAttempt::create([
                'user_id' => $user->id,
                'crime_location_id' => $location->id,
                'success' => $isSuccess,
                'jailed' => $jailed,
                'cash_earned' => $cashEarned,
                'exp_earned' => $expEarned,
                'respect_earned' => $respectEarned,
                'result_message' => $isSuccess ? 'Success!' : 'Failed!',
                'attempted_at' => now(),
            ]);
            
            // If jailed, stop further attempts
            if ($jailed) {
                break;
            }
        }
        
        // Update user stats
        $user->increment('cash', $totalCash);
        $user->increment('experience', $totalExp);
        $user->decrement('energy', $totalEnergy);
        
        // Set cooldown timer
        $user->setTimer('crime', $location->cooldown_seconds);
        
        // Handle jail if caught
        if ($jailed) {
            $jailTime = 300; // 5 minutes base jail time
            $user->setTimer('jail', $jailTime);
            
            return response()->json([
                'success' => false,
                'jailed' => true,
                'message' => "You were caught and sent to jail for {$jailTime} seconds!",
                'results' => [
                    'total_attempts' => $successCount + $failCount,
                    'successes' => $successCount,
                    'fails' => $failCount,
                    'cash_earned' => $totalCash,
                    'exp_earned' => $totalExp,
                ],
                'player' => [
                    'cash' => $user->cash,
                    'energy' => $user->energy,
                    'experience' => $user->experience,
                    'level' => $user->level,
                ],
            ]);
        }
        
        $user->refresh();
        $newCooldown = max(0, now()->diffInSeconds($user->getTimer('crime')->expires_at, false));
        
        return response()->json([
            'success' => true,
            'message' => $successCount > 0 
                ? "Successfully committed {$successCount} crime(s)! Earned \${$totalCash} and {$totalExp} XP."
                : "All attempts failed.",
            'results' => [
                'total_attempts' => $successCount + $failCount,
                'successes' => $successCount,
                'fails' => $failCount,
                'cash_earned' => $totalCash,
                'exp_earned' => $totalExp,
                'respect_earned' => $totalRespect,
            ],
            'player' => [
                'cash' => $user->cash,
                'energy' => $user->energy,
                'experience' => $user->experience,
                'level' => $user->level,
            ],
            'cooldown' => $newCooldown,
        ]);
    }
    
    /**
     * Get crime location statistics
     */
    public function stats(Request $request, int $id)
    {
        $user = $request->user();
        $location = CrimeLocation::findOrFail($id);
        
        $stats = $location->getUserStats($user);
        
        return response()->json([
            'stats' => $stats,
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
            ],
        ]);
    }
}
