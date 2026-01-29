<?php

use App\Modules\Missions\MissionsModule;

return [
    /**
     * Triggered when a mission is started
     */
    'OnMissionStarted' => function ($data) {
        // Log mission start
        \Log::info('Mission started', [
            'mission_id' => $data['mission']->id,
            'player' => $data['player']->username,
        ]);
        
        return $data;
    },
    
    /**
     * Triggered when a mission is completed
     */
    'OnMissionCompleted' => function ($data) {
        // Log completion and award bonuses
        \Log::info('Mission completed', [
            'mission_id' => $data['mission']->id,
            'player' => $data['player']->username,
            'rewards' => $data['rewards'],
        ]);
        
        return $data;
    },
    
    /**
     * Alter available missions before display
     */
    'alterAvailableMissions' => function ($data) {
        // Can be used to filter or modify available missions
        return $data;
    },
    
    'alterActiveMissions' => function ($data) {
        // Can be used to modify active missions display
        return $data;
    },
];
