<?php

use App\Facades\Hook;

// Quest start hook
Hook::register('OnQuestStart', function ($data) {
    return $data;
});

// Quest complete hook
Hook::register('OnQuestComplete', function ($data) {
    // Could trigger achievements, etc.
    return $data;
});

// Objective complete hook
Hook::register('OnObjectiveComplete', function ($data) {
    return $data;
});
