<?php

namespace App\Core\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('currentRank', 'location');

        $pluginService = app(\App\Core\Services\PluginService::class);

        // Get all enabled plugins and mark which are locked/unlocked
        $allPlugins = \App\Core\Models\Plugin::where('enabled', true)
            ->orderBy('order')
            ->get()
            ->map(function($plugin) use ($user) {
                $isLocked = $user->level < $plugin->required_level;
                return [
                    'id' => $plugin->id,
                    'name' => $plugin->name,
                    'display_name' => $plugin->display_name,
                    'description' => $plugin->description,
                    'icon' => $plugin->icon,
                    'route_name' => $plugin->route_name,
                    'required_level' => $plugin->required_level,
                    'locked' => $isLocked,
                    'order' => $plugin->order,
                ];
            });

        $navigationItems = $pluginService->getNavigationItems($user);

        $dailyRewardService = app(\App\Plugins\DailyRewards\Services\DailyRewardService::class);
        $dailyReward = $dailyRewardService->getRewardInfo($user);

        $timerService = app(\App\Core\Services\TimerService::class);
        $activeTimers = $timerService->getActiveTimers($user);

        $notificationService = app(\App\Core\Services\NotificationService::class);
        $unreadNotifications = $notificationService->getUnreadCount($user);

        return response()->json([
            'player' => $user,
            'modules' => $allPlugins, // Keep 'modules' key for frontend compatibility
            'plugins' => $allPlugins,
            'navigationItems' => $navigationItems,
            'dailyReward' => $dailyReward,
            'activeTimers' => $activeTimers,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }
}
