<?php

/**
 * Organized Crime Module Hooks
 * 
 * Registers hooks for the Organized Crime module
 */

use App\Facades\Hook;

// Add organized crimes to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user || !$user->gang_id) return [];
    
    return [
        'organized-crimes' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('organized-crimes.index'),
                    'text' => 'Organized Crimes',
                    'icon' => '💼',
                    'timer' => $user->gang ? $user->gang->getTimer('organized_crime') : null,
                    'badge' => null,
                    'sort' => 450,
                ],
            ],
        ],
    ];
}, 45);

// Track organized crime statistics
Hook::register('afterOrganizedCrimeAttempt', function ($data) {
    $user = $data['user'];
    $gang = $data['gang'];
    $crime = $data['crime'];
    $success = $data['success'];
    
    // Fire event for mission tracking
    event(new \App\Events\Module\OnOrganizedCrimeAttempt(
        $user,
        $gang,
        $crime->name,
        $success,
        $data['result']['cash_earned'] ?? 0,
        $data['result']['respect_gained'] ?? 0
    ));
}, 10);

// Modify organized crime data display
Hook::register('alterOrganizedCrimeData', function ($data) {
    // Can be used by other modules to modify crime display
    return $data;
}, 10);
