<?php

/**
 * Crimes Module Hooks
 * 
 * Registers hooks for the Crimes module
 * Similar to V2's crimes.hooks.php
 */

use App\Facades\Hook;

// Add crimes to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'crimes' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('crimes.index'),
                    'text' => 'Crimes',
                    'icon' => '🔫',
                    'timer' => $user->getTimer('crime'),
                    'badge' => null,
                    'sort' => 100,
                ],
            ],
        ],
    ];
}, 10);

// Track crime statistics for missions
Hook::register('afterCrimeAttempt', function ($data) {
    $user = $data['user'];
    $crime = $data['crime'];
    $success = $data['success'];
    
    // Update mission progress if applicable
    if ($success) {
        // This would integrate with missions module
        event(new \App\Events\Module\OnCrimeCommit(
            $user,
            $crime->name,
            $success,
            $data['result']['cash_earned'] ?? 0,
            $data['result']['exp_earned'] ?? 0
        ));
    }
}, 10);

// Modify crime data before display
Hook::register('alterCrimeData', function ($data) {
    // Add any custom crime data modifications here
    return $data;
}, 10);

// Modify success rate based on equipment, buffs, etc.
Hook::register('modifyCrimeSuccessRate', function ($data) {
    $user = $data['user'];
    $finalRate = $data['final_rate'];
    
    // Example: Bonus from gang membership
    if ($user->gang_id) {
        $finalRate += 0.05; // 5% bonus
    }
    
    $data['final_rate'] = $finalRate;
    return $data;
}, 10);

// Module load hook
Hook::register('moduleLoad', function ($data) {
    if ($data['module'] === 'crimes') {
        // Check if user is in jail
        if ($data['user']->isInJail()) {
            return [
                'redirect' => route('jail.index'),
                'message' => 'You cannot commit crimes while in jail',
            ];
        }
    }
    return $data;
}, 10);
