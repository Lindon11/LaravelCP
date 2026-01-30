<?php

use App\Modules\Achievements\AchievementsModule;

return [
    /**
     * Triggered when a player earns an achievement
     */
    'OnAchievementEarned' => function ($data) {
        // Log achievement earned
        \Log::info('Achievement earned', [
            'achievement_id' => $data['achievement']->id,
            'player' => $data['player']->username,
            'type' => $data['achievement']->type,
        ]);
        
        // Could trigger notifications, awards, etc.
        
        return $data;
    },
    
    /**
     * Alter achievements data before display
     */
    'alterAchievements' => function ($data) {
        // Can be used to modify achievement display or add custom achievements
        return $data;
    },
];
