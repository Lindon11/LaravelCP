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
        
        // Get all enabled modules and mark which are locked/unlocked
        $allModules = \App\Models\Module::where('enabled', true)
            ->orderBy('order')
            ->get()
            ->map(function($module) use ($user) {
                $isLocked = $user->level < $module->required_level;
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'route_name' => $module->route_name,
                    'required_level' => $module->required_level,
                    'locked' => $isLocked,
                    'order' => $module->order,
                ];
            });
        
        $navigationItems = $moduleService->getNavigationItems($user);
        
        $dailyRewardService = app(\App\Services\DailyRewardService::class);
        $dailyReward = $dailyRewardService->getRewardInfo($user);
        
        $timerService = app(\App\Services\TimerService::class);
        $activeTimers = $timerService->getActiveTimers($user);
        
        $notificationService = app(\App\Services\NotificationService::class);
        $unreadNotifications = $notificationService->getUnreadCount($user);
        
        return response()->json([
            'player' => $user,
            'modules' => $allModules,
            'navigationItems' => $navigationItems,
            'dailyReward' => $dailyReward,
            'activeTimers' => $activeTimers,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}
