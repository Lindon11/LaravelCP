<?php

/**
 * Tickets Module Hooks
 */

return [
    'OnTicketCreated' => function($data) {
        // Could notify staff, send email
        return $data;
    },
    
    'OnTicketReplied' => function($data) {
        // Could notify user/staff of reply
        return $data;
    },
    
    'OnTicketClosed' => function($data) {
        return $data;
    },
    
    'OnTicketAssigned' => function($data) {
        // Could notify assigned staff
        return $data;
    },
    
    'moduleLoad' => function() {
        // Initialization
    },
];
