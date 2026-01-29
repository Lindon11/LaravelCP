<?php

namespace App\Modules\Achievements;

use App\Modules\Module;

/**
 * Achievements Module
 * 
 * Handles achievement tracking and rewards
 * Tracks player accomplishments and provides badges/rewards
 */
class AchievementsModule extends Module
{
    protected string $name = 'Achievements';
    
    public function construct(): void
    {
        $this->config = [
            'bonus_cash_multiplier' => 1000,
            'bonus_xp_multiplier' => 100,
            'achievement_types' => [
                'combat',
                'wealth',
                'social',
                'missions',
                'special',
            ],
        ];
    }
    
    /**
     * Get all achievements with player progress
     */
    public function getUserAchievements($user): array
    {
        $achievements = \App\Models\Achievement::with(['userProgress' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])->get();
        
        return $this->applyModuleHook('alterAchievements', [
            'achievements' => $achievements,
            'user' => $user,
        ]);
    }
    
    /**
     * Check if player earned an achievement
     */
    public function checkAchievement($user, $achievementType, $value): bool
    {
        // Logic to check achievement progress
        return false;
    }
    
    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'total' => \App\Models\Achievement::count(),
                'earned' => 0,
                'percentage' => 0,
            ];
        }
        
        $total = \App\Models\Achievement::count();
        $earned = \App\Models\UserAchievement::where('user_id', $user->id)
            ->where('is_earned', true)
            ->count();
        
        return [
            'total' => $total,
            'earned' => $earned,
            'percentage' => $total > 0 ? round(($earned / $total) * 100) : 0,
        ];
    }
}
