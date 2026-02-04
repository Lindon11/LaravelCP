<?php

/**
 * Achievements Module Hooks
 */

return [
    // Called when an achievement is earned
    'OnAchievementEarned' => function($data) {
        // Log achievement, send notification, etc.
        return $data;
    },
    
    // Called when progress is updated
    'OnProgressUpdate' => function($data) {
        return $data;
    },
    
    // Modify achievement rewards before giving
    'alterAchievementRewards' => function($data) {
        // Could apply membership bonuses, etc.
        return $data;
    },
    
    // Called when module loads
    'moduleLoad' => function() {
        // Initialization
    },
];
