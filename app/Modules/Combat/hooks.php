<?php

/**
 * Combat Module Hooks
 * 
 * Registers hooks for the Combat module
 */

use App\Facades\Hook;

// Add combat to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'combat' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('combat.index'),
                    'text' => 'Combat',
                    'icon' => '⚔️',
                    'timer' => $user->getTimer('combat'),
                    'badge' => null,
                    'sort' => 600,
                ],
            ],
        ],
    ];
}, 60);

// Track combat events
Hook::register('afterCombat', function ($data) {
    $attacker = $data['attacker'];
    $defender = $data['defender'];
    $result = $data['result'];
    
    // Fire event for mission tracking
    event(new \App\Events\Module\OnCombat(
        $attacker,
        $defender,
        $result['winner'],
        $result['killed'] ?? false,
        $result['cash_stolen'] ?? 0
    ));
    
    // If someone was killed, trigger kill event
    if ($result['killed'] && $result['winner'] === 'attacker') {
        event(new \App\Events\Module\OnPlayerKilled(
            $attacker,
            $defender
        ));
    }
}, 10);

// Modify combat target data
Hook::register('alterCombatTarget', function ($data) {
    // Can be used by other modules to modify target display
    return $data;
}, 10);

// Modify combat power calculation
Hook::register('modifyCombatPower', function ($data) {
    // Can be used by other modules to affect combat power
    return $data;
}, 10);
