<?php

/**
 * Bounty Module Hooks
 * 
 * Registers hooks for the Bounty module
 */

use App\Facades\Hook;

// Add bounties to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'bounties' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('bounties.index'),
                    'text' => 'Bounties',
                    'icon' => '🎯',
                    'timer' => $user->getTimer('bounty_place'),
                    'badge' => null,
                    'sort' => 500,
                ],
            ],
        ],
    ];
}, 50);

// Track bounty placement
Hook::register('afterBountyPlace', function ($data) {
    $user = $data['user'];
    $target = $data['target'];
    $bounty = $data['bounty'];
    
    // Fire event for mission tracking
    event(new \App\Events\Module\OnBountyPlaced(
        $user,
        $target,
        $bounty->amount
    ));
}, 10);

// Track bounty claims
Hook::register('afterBountyClaim', function ($data) {
    $killer = $data['killer'];
    $victim = $data['victim'];
    $bounty = $data['bounty'];
    
    event(new \App\Events\Module\OnBountyClaimed(
        $killer,
        $victim,
        $bounty->amount
    ));
}, 10);

// Modify bounty data display
Hook::register('alterBountyData', function ($data) {
    // Can be used by other modules to modify bounty display
    return $data;
}, 10);
