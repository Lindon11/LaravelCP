<?php

/**
 * Jail Module Hooks
 * 
 * Registers hooks for the Jail module
 */

use App\Facades\Hook;

// Add jail to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'jail' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('jail.index'),
                    'text' => 'Jail',
                    'icon' => '⛓️',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 600,
                ],
            ],
        ],
    ];
}, 10);

// Track bust outs for missions
Hook::register('afterBustOut', function ($data) {
    $actor = $data['actor'];
    $target = $data['target'];
    
    event(new \App\Events\Module\OnBustOut(
        $actor,
        $target
    ));
}, 10);

// Track bail outs for missions
Hook::register('afterBailOut', function ($data) {
    $user = $data['user'];
    $cost = $data['cost'];
    
    event(new \App\Events\Module\OnBailOut(
        $user,
        $cost
    ));
}, 10);
