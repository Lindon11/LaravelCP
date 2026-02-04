<?php

use App\Facades\Hook;

// Message sent hook
Hook::register('OnMessageSent', function ($data) {
    // Could send push notification, email notification, etc.
    return $data;
});

// Message read hook
Hook::register('OnMessageRead', function ($data) {
    return $data;
});
