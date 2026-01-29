<?php

use App\Modules\Leaderboards\LeaderboardsModule;

return [
    /**
     * Triggered when rankings are updated
     */
    'OnRankingUpdated' => function ($data) {
        // Log ranking update
        \Log::info('Ranking updated', [
            'type' => $data['type'],
            'player' => $data['player']->username,
            'new_rank' => $data['rank'],
        ]);
        
        return $data;
    },
    
    /**
     * Alter leaderboard data before display
     */
    'alterLeaderboard' => function ($data) {
        // Can be used to filter or modify leaderboard display
        return $data;
    },
];
