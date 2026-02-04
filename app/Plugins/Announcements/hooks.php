<?php

/**
 * Announcements Module Hooks
 */

return [
    'OnAnnouncementCreated' => function($data) {
        // Could send emails, push notifications
        return $data;
    },
    
    'OnAnnouncementViewed' => function($data) {
        // Could track view counts, analytics
        return $data;
    },
    
    // Filter announcements (membership-based visibility, etc.)
    'filterAnnouncements' => function($data) {
        return $data;
    },
    
    'moduleLoad' => function() {
        // Initialization
    },
];
