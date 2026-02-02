<?php

/**
 * Gym Module Hooks
 * 
 * Registers hooks for the Gym module
 */

use App\Facades\Hook;

// Add gym to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'gym' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('gym.index'),
                    'text' => 'Gym',
                    'icon' => '💪',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 300,
                ],
            ],
        ],
    ];
}, 10);

// Track training statistics for missions
Hook::register('afterTrain', function ($data) {
    $user = $data['user'];
    $attribute = $data['attribute'];
    $times = $data['times'];
    
    // Update mission progress if applicable
    event(new \App\Events\Module\OnTrain(
        $user,
        $attribute,
        $times
    ));
}, 10);
