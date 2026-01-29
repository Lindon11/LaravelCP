<?php

use App\Modules\DailyRewards\DailyRewardsModule;

return [
    /**
     * Triggered when a player claims their daily reward
     */
    'OnDailyRewardClaimed' => function ($data) {
        // Log daily reward claim
        \Log::info('Daily reward claimed', [
            'player' => $data['player']->username,
            'streak' => $data['streak'],
            'rewards' => $data['rewards'],
        ]);
        
        return $data;
    },
    
    /**
     * Triggered when a player reaches a streak milestone
     */
    'OnStreakMilestone' => function ($data) {
        // Log milestone achievement
        \Log::info('Streak milestone reached', [
            'player' => $data['player']->username,
            'milestone' => $data['milestone'],
            'bonus_rewards' => $data['bonus'],
        ]);
        
        // Could trigger special notifications or achievements
        
        return $data;
    },
    
    /**
     * Alter daily reward before awarding
     */
    'alterDailyReward' => function ($data) {
        // Can be used to modify rewards based on special events
        return $data;
    },
];
