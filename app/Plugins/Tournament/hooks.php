<?php

/*
|--------------------------------------------------------------------------
| Tournament Plugin Hooks
|--------------------------------------------------------------------------
|
| Register hooks and event listeners for the Tournament plugin
|
*/

use App\Facades\Hook;
use App\Plugins\Tournament\Models\Tournament;

// Add tournament to navigation menu
Hook::register('customMenus', function ($user) {
    if (!$user) return [];

    return [
        'tournaments' => [
            'title' => 'Compete',
            'items' => [
                [
                    'url' => '/tournaments',
                    'text' => 'Tournaments',
                    'icon' => '🏆',
                    'timer' => null,
                    'badge' => null,
                    'sort' => 500,
                ],
            ],
        ],
    ];
}, 50);
