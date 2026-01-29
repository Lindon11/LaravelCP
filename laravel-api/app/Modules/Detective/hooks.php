<?php

/**
 * Detective Module Hooks
 * 
 * Registers hooks for the Detective module
 */

use App\Facades\Hook;

// Add detective to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'detective' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('detective.index'),
                    'text' => 'Detective',
                    'icon' => '🔍',
                    'timer' => $user->getTimer('detective_hire'),
                    'badge' => null,
                    'sort' => 550,
                ],
            ],
        ],
    ];
}, 55);

// Track detective hiring
Hook::register('afterDetectiveHire', function ($data) {
    $user = $data['user'];
    $target = $data['target'];
    $report = $data['report'];
    
    // Fire event for mission tracking
    event(new \App\Events\Module\OnDetectiveHired(
        $user,
        $target,
        $report->id
    ));
}, 10);

// Modify detective report data
Hook::register('modifyDetectiveReport', function ($data) {
    // Can be used by other modules to add info to reports
    return $data;
}, 10);
