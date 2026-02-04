<?php

use App\Facades\Hook;

// Event start hook
Hook::register('OnEventStart', function ($data) {
    // Notify all participants
    if (isset($data['event'])) {
        $event = $data['event'];
        // Send notifications to participants
    }
    return $data;
});

// Event end hook
Hook::register('OnEventEnd', function ($data) {
    // Process rewards and notifications
    return $data;
});
