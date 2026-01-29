<?php

/**
 * Theft Module Hooks
 * 
 * Registers hooks for the Theft module
 */

use App\Facades\Hook;

// Add theft to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'theft' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('theft.index'),
                    'text' => 'Car Theft',
                    'icon' => '🚗',
                    'timer' => $user->getTimer('theft'),
                    'badge' => null,
                    'sort' => 200,
                ],
                [
                    'url' => route('theft.garage'),
                    'text' => 'Garage',
                    'icon' => '🏠',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 201,
                ],
            ],
        ],
    ];
}, 10);

// Track theft statistics for missions
Hook::register('afterTheftAttempt', function ($data) {
    $user = $data['user'];
    $result = $data['result'];
    
    // Update mission progress if applicable
    if ($result['success']) {
        event(new \App\Events\Module\OnTheftSuccess(
            $user,
            $data['theft_type'],
            $result
        ));
    }
}, 10);

// Track car sales for missions
Hook::register('afterCarSell', function ($data) {
    $user = $data['user'];
    $value = $data['value'];
    
    event(new \App\Events\Module\OnCarSold(
        $user,
        $value
    ));
}, 10);
