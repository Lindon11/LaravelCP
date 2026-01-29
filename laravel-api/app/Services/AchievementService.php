<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AchievementService
{
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
                // Give rewards
                $user->cash += $achievement->reward_cash;
                $user->experience += $achievement->reward_xp;
                $user->save();
                
                $earnedAchievements[] = [
                    'achievement' => $achievement,
                    'rewards' => [
                        'cash' => $achievement->reward_cash,
                        'xp' => $achievement->reward_xp
                    ]
                ];
            }
        }
        
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
                'is_earned' => !is_null($userProgress->earned_at ?? null),
                'percentage' => min(100, round((($userProgress->progress ?? 0) / $achievement->requirement) * 100))
            ];
        });
    }
}
