<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('currentRank', 'location');
        
        $moduleService = app(\App\Services\ModuleService::class);
        $modules = $moduleService->getModulesForPlayer($user);
        $navigationItems = $moduleService->getNavigationItems($user);
        
        $dailyRewardService = app(\App\Services\DailyRewardService::class);
        $dailyReward = $dailyRewardService->getRewardInfo($user);
        
        $timerService = app(\App\Services\TimerService::class);
        $activeTimers = $timerService->getActiveTimers($user);
        
        $notificationService = app(\App\Services\NotificationService::class);
        $unreadNotifications = $notificationService->getUnreadCount($user);
        
        return response()->json([
            'player' => $user,
            'modules' => $modules,
            'navigationItems' => $navigationItems,
            'dailyReward' => $dailyReward,
            'activeTimers' => $activeTimers,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}
