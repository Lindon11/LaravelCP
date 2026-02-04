<?php

namespace App\Plugins\Achievements;

use App\Plugins\Plugin;
use App\Plugins\Achievements\Models\Achievement;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;
use App\Facades\Hook;

/**
 * Achievements Module
 * 
 * Handles player achievements, progress tracking, and rewards
 */
class AchievementsModule extends Plugin
{
    protected string $name = 'Achievements';
    
    public function construct(): void
    {
        $this->config = [
            'auto_check' => true, // Automatically check achievements on actions
            'show_notifications' => true, // Show notifications when achievements are earned
        ];
    }
    
    /**
     * Check and update progress for a specific achievement type
     */
    public function checkProgress(User $user, string $type, int $currentValue): array
    {
        $earnedAchievements = [];
        
        $achievements = Achievement::where('type', $type)
            ->orderBy('requirement')
            ->get();
            
        foreach ($achievements as $achievement) {
            $userAchievement = DB::table('user_achievements')
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();
                
            // Skip if already earned
            if ($userAchievement && $userAchievement->earned_at) {
                continue;
            }
            
            // Update progress
            if (!$userAchievement) {
                DB::table('user_achievements')->insert([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'progress' => $currentValue,
                    'earned_at' => $currentValue >= $achievement->requirement ? now() : null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('user_achievements')
                    ->where('user_id', $user->id)
                    ->where('achievement_id', $achievement->id)
                    ->update([
                        'progress' => $currentValue,
                        'earned_at' => $currentValue >= $achievement->requirement ? now() : null,
                        'updated_at' => now()
                    ]);
            }
            
            // Check if just earned
            if ($currentValue >= $achievement->requirement && !$userAchievement?->earned_at) {
                // Apply hook to modify rewards
                $rewards = $this->applyModuleHook('alterAchievementRewards', [
                    'cash' => $achievement->reward_cash,
                    'xp' => $achievement->reward_xp,
                    'user' => $user,
                    'achievement' => $achievement,
                ]);
                
                // Give rewards
                $user->cash += $rewards['cash'] ?? $achievement->reward_cash;
                $user->experience += $rewards['xp'] ?? $achievement->reward_xp;
                $user->save();
                
                $earnedData = [
                    'achievement' => $achievement,
                    'rewards' => [
                        'cash' => $rewards['cash'] ?? $achievement->reward_cash,
                        'xp' => $rewards['xp'] ?? $achievement->reward_xp
                    ]
                ];
                
                $earnedAchievements[] = $earnedData;
                
                // Fire hook for other modules to react
                $this->applyModuleHook('OnAchievementEarned', $earnedData);
            }
        }
        
        // Fire progress update hook
        $this->applyModuleHook('OnProgressUpdate', [
            'user' => $user,
            'type' => $type,
            'value' => $currentValue,
        ]);
        
        return $earnedAchievements;
    }
    
    /**
     * Get all achievements with user progress
     */
    public function getUserAchievements(User $user)
    {
        $achievements = Achievement::orderBy('sort_order')->get();
        
        return $achievements->map(function ($achievement) use ($user) {
            $userProgress = DB::table('user_achievements')
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();
                
            return [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'type' => $achievement->type,
                'requirement' => $achievement->requirement,
                'reward_cash' => $achievement->reward_cash,
                'reward_xp' => $achievement->reward_xp,
                'icon' => $achievement->icon,
                'progress' => $userProgress->progress ?? 0,
                'earned_at' => $userProgress->earned_at ?? null,
                'unlocked_at' => $userProgress->earned_at ?? null,
                'is_earned' => !is_null($userProgress->earned_at ?? null),
                'percentage' => min(100, round((($userProgress->progress ?? 0) / max(1, $achievement->requirement)) * 100))
            ];
        });
    }
    
    /**
     * Get achievement statistics for a user
     */
    public function getStats(User $user): array
    {
        $achievements = $this->getUserAchievements($user);
        
        return [
            'total' => $achievements->count(),
            'earned' => $achievements->where('is_earned', true)->count(),
            'percentage' => $achievements->count() > 0
                ? round(($achievements->where('is_earned', true)->count() / $achievements->count()) * 100)
                : 0
        ];
    }
    
    /**
     * Get achievements grouped by type
     */
    public function getGroupedAchievements(User $user)
    {
        return $this->getUserAchievements($user)->groupBy('type');
    }
    
    /**
     * Get recently earned achievements
     */
    public function getRecentlyEarned(User $user, int $limit = 5)
    {
        return DB::table('user_achievements')
            ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
            ->where('user_achievements.user_id', $user->id)
            ->whereNotNull('user_achievements.earned_at')
            ->orderBy('user_achievements.earned_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
