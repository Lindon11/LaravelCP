<?php

/**
 * Hospital Module Hooks
 * 
 * Registers hooks for the Hospital module
 */

use App\Facades\Hook;

// Add hospital to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'hospital' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('hospital.index'),
                    'text' => 'Hospital',
                    'icon' => '🏥',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 500,
                ],
            ],
        ],
    ];
}, 10);

// Track healing for missions
Hook::register('afterHeal', function ($data) {
    $user = $data['user'];
    $amount = $data['amount'];
    $cost = $data['cost'];
    
    event(new \App\Events\Module\OnHeal(
        $user,
        $amount,
        $cost
    ));
}, 10);

// Track full heals for missions
Hook::register('afterHealFull', function ($data) {
    $user = $data['user'];
    $cost = $data['cost'];
    
    event(new \App\Events\Module\OnHealFull(
        $user,
        $cost
    ));
}, 10);
