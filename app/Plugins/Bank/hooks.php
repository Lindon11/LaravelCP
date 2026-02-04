<?php

/**
 * Bank Module Hooks
 * 
 * Registers hooks for the Bank module
 */

use App\Facades\Hook;

// Add bank to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'bank' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('bank.index'),
                    'text' => 'Bank',
                    'icon' => '🏦',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 400,
                ],
            ],
        ],
    ];
}, 10);

// Track deposits for missions
Hook::register('afterBankDeposit', function ($data) {
    $user = $data['user'];
    $amount = $data['amount'];
    
    event(new \App\Events\Module\OnBankDeposit(
        $user,
        $amount
    ));
}, 10);

// Track withdrawals for missions
Hook::register('afterBankWithdraw', function ($data) {
    $user = $data['user'];
    $amount = $data['amount'];
    
    event(new \App\Events\Module\OnBankWithdraw(
        $user,
        $amount
    ));
}, 10);

// Track transfers for missions
Hook::register('afterBankTransfer', function ($data) {
    $user = $data['user'];
    $recipient = $data['recipient'];
    $amount = $data['amount'];
    
    event(new \App\Events\Module\OnBankTransfer(
        $user,
        $recipient,
        $amount
    ));
}, 10);
