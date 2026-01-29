<?php

/**
 * Example Module Hooks File
 * 
 * This file demonstrates how modules can register hooks
 * Similar to V2's {module}.hooks.php files
 * 
 * Place in: app/Modules/{ModuleName}/hooks.php
 */

use App\Facades\Hook;

// Example 1: Add menu items
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'crimes' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('crimes.index'),
                    'text' => 'Crimes',
                    'icon' => 'heroicon-o-fire',
                    'timer' => $user->getTimer('crime'),
                    'sort' => 100,
                ],
                [
                    'url' => route('gym.index'),
                    'text' => 'Gym',
                    'icon' => 'heroicon-o-bolt',
                    'timer' => $user->getTimer('gym'),
                    'sort' => 200,
                ],
            ],
            'sort' => 100,
        ]
    ];
});

// Example 2: Modify crime data (membership benefits)
Hook::register('alterModuleData', function ($data) {
    if ($data['module'] === 'crimes' && !$data['user']->checkTimer('membership')) {
        // Increase success rate for members
        $data['data']['success_rate'] = floor($data['data']['success_rate'] * 1.25);
        if ($data['data']['success_rate'] > 100) {
            $data['data']['success_rate'] = 100;
        }
    }
    return $data;
});

// Example 3: Track user actions
Hook::register('afterUserAction', function ($action) {
    if ($action['module'] === 'crimes' && $action['success']) {
        // Award achievement points
        // Update statistics
        // Log for leaderboards
        \Log::info('User action tracked', $action);
    }
    return $action;
});

// Example 4: Module load interceptor (jail/hospital checks)
Hook::register('moduleLoad', function ($moduleName) {
    $user = auth()->user();
    
    if (!$user) return $moduleName;
    
    // Check if user is in hospital
    if ($user->isInHospital() && !in_array($moduleName, ['hospital', 'logout', 'profile'])) {
        return 'hospital';
    }
    
    // Check if user is in jail
    if ($user->isInJail() && !in_array($moduleName, ['jail', 'logout', 'profile'])) {
        return 'jail';
    }
    
    return $moduleName;
});

// Example 5: Currency formatting
Hook::register('currencyFormat', function ($money) {
    // Custom formatting for specific ranges
    if ($money >= 1000000) {
        return '$' . number_format($money / 1000000, 2) . 'M';
    } elseif ($money >= 1000) {
        return '$' . number_format($money / 1000, 1) . 'K';
    }
    return '$' . number_format($money);
});

// Example 6: Add admin widgets
Hook::register('adminWidget-stats', function ($user) {
    return [
        'title' => 'Crime Statistics',
        'size' => 6,
        'sort' => 100,
        'data' => [
            'total_crimes' => \DB::table('crime_logs')->count(),
            'success_rate' => \DB::table('crime_logs')->where('success', true)->count() / max(\DB::table('crime_logs')->count(), 1) * 100,
            'today' => \DB::table('crime_logs')->whereDate('created_at', today())->count(),
        ],
    ];
});

// Example 7: Before/After hooks for specific actions
Hook::register('beforeCrimeAttempt', function ($crime) {
    // Validate requirements
    // Check cooldowns
    // Apply modifiers
    return $crime;
});

Hook::register('afterCrimeSuccess', function ($result) {
    // Award money/items
    // Update stats
    // Trigger notifications
    return $result;
});

// Example 8: Template data modification
Hook::register('alterTemplateData', function ($data) {
    if (isset($data['module']) && $data['module'] === 'dashboard') {
        // Add custom data to dashboard
        $data['latest_crimes'] = \DB::table('crime_logs')
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get();
    }
    return $data;
});
