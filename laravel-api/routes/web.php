<?php

use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Installer Routes (public access)
Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'databaseStore'])->name('database.store');
    Route::get('/settings', [InstallerController::class, 'settings'])->name('settings');
    Route::post('/settings', [InstallerController::class, 'settingsStore'])->name('settings.store');
    Route::get('/install', [InstallerController::class, 'install'])->name('install');
    Route::post('/install/process', [InstallerController::class, 'installProcess'])->name('install.process');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'adminStore'])->name('admin.store');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        $moduleService = app(\App\Services\ModuleService::class);
        $modules = $moduleService->getModulesForPlayer($user);
        $navigationItems = $moduleService->getNavigationItems($user);
        
        $dailyRewardService = app(\App\Services\DailyRewardService::class);
        $dailyReward = $dailyRewardService->getRewardInfo($user);
        
        $timerService = app(\App\Services\TimerService::class);
        $activeTimers = $timerService->getActiveTimers($user);
        
        $notificationService = app(\App\Services\NotificationService::class);
        $unreadNotifications = $notificationService->getUnreadCount($user);
        
        return Inertia::render('DashboardGame', [
            'player' => $user,
            'modules' => $modules,
            'navigationItems' => $navigationItems,
            'dailyReward' => $dailyReward,
            'activeTimers' => $activeTimers,
            'unreadNotifications' => $unreadNotifications
        ]);
    })->name('dashboard');

    // Chat route
    Route::get('/chat', function () {
        return Inertia::render('Chat');
    })->name('chat');

    // All game features are now handled by modules in app/Modules/
    // Module routes are auto-loaded by ModuleServiceProvider
    
    // Player Profile routes
    Route::get('/player/{id}', [ProfileController::class, 'show'])->name('player.profile');

    // Notification routes
    Route::prefix('notifications')->controller(\App\Http\Controllers\NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('notifications.index');
        Route::get('/recent', 'recent')->name('notifications.recent');
        Route::get('/unread-count', 'unreadCount')->name('notifications.unread-count');
        Route::post('/{id}/read', 'markAsRead')->name('notifications.mark-read');
        Route::post('/mark-all-read', 'markAllAsRead')->name('notifications.mark-all-read');
        Route::delete('/{id}', 'delete')->name('notifications.delete');
        Route::delete('/read/clear', 'deleteRead')->name('notifications.delete-read');
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::middleware(['permission:manage-players'])->group(function () {
            Route::get('/moderation', [ModerationController::class, 'index'])->name('admin.moderation.index');
            Route::post('/moderation/ban', [ModerationController::class, 'banPlayer'])->name('admin.moderation.ban');
            Route::post('/moderation/unban/{ban}', [ModerationController::class, 'unbanPlayer'])->name('admin.moderation.unban');
            Route::post('/moderation/warn', [ModerationController::class, 'warnPlayer'])->name('admin.moderation.warn');
            Route::post('/moderation/adjust-stats', [ModerationController::class, 'adjustStats'])->name('admin.moderation.adjust-stats');
            Route::post('/moderation/grant-item', [ModerationController::class, 'grantItem'])->name('admin.moderation.grant-item');
            Route::post('/moderation/announcement', [ModerationController::class, 'sendAnnouncement'])->name('admin.moderation.announcement');
            Route::post('/moderation/mass-email', [ModerationController::class, 'sendMassEmail'])->name('admin.moderation.mass-email');
        });
        
        Route::middleware(['permission:manage-system'])->group(function () {
            Route::get('/system', [SystemController::class, 'dashboard'])->name('admin.system.dashboard');
            Route::get('/system/activity', [SystemController::class, 'playerActivity'])->name('admin.system.activity');
            Route::get('/system/health', [SystemController::class, 'serverHealth'])->name('admin.system.health');
        });
    });
});
