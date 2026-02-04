<?php

/**
 * Gang Module Hooks
 * 
 * Registers hooks for the Gang module
 */

use App\Facades\Hook;

// Add gangs to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'gangs' => [
            'title' => 'Social',
            'items' => [
                [
                    'url' => route('gangs.index'),
                    'text' => 'Gangs',
                    'icon' => '👥',
                    'timer' => null,
                    'badge' => $user->gang ? $user->gang->name : null,
                    'sort' => 400,
                ],
            ],
        ],
    ];
}, 40);

// Track gang statistics for missions
Hook::register('afterGangCreate', function ($data) {
    $user = $data['user'];
    $gang = $data['gang'];
    
    // Fire event for mission tracking
    event(new \App\Events\Module\OnGangCreate(
        $user,
        $gang->name,
        $gang->tag
    ));
}, 10);

// Handle gang join events
Hook::register('afterGangJoin', function ($data) {
    $user = $data['user'];
    $gang = $data['gang'];
    
    event(new \App\Events\Module\OnGangJoin(
        $user,
        $gang
    ));
}, 10);

// Handle gang leave events
Hook::register('afterGangLeave', function ($data) {
    $user = $data['user'];
    $gang = $data['gang'];
    
    event(new \App\Events\Module\OnGangLeave(
        $user,
        $gang
    ));
}, 10);

// Modify gang data display
Hook::register('alterGangData', function ($data) {
    // Can be used by other modules to modify gang display
    return $data;
}, 10);
