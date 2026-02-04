<?php

/**
 * Chat Module Hooks
 */

return [
    'OnMessageSent' => function($data) {
        // Could trigger notifications, achievements
        return $data;
    },
    
    'OnMessageDeleted' => function($data) {
        return $data;
    },
    
    'OnChannelCreated' => function($data) {
        return $data;
    },
    
    'OnReaction' => function($data) {
        return $data;
    },
    
    // Filter/modify message content (profanity filter, etc.)
    'filterMessageContent' => function($data) {
        return $data;
    },
    
    'moduleLoad' => function() {
        // Initialization
    },
];
