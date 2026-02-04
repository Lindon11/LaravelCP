<?php

use App\Core\Http\Controllers\AuthController;
use App\Plugins\Chat\Controllers\ChatController;
use App\Plugins\Chat\Controllers\ChatChannelController;
use App\Plugins\Chat\Controllers\ChatMessageController;
use App\Plugins\Chat\Controllers\DirectMessageController;
use App\Core\Http\Controllers\EmojiController;
use App\Core\Http\Controllers\PluginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes (rate limited to prevent brute force)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset Routes
    Route::post('/forgot-password', [\App\Core\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink']);
    Route::post('/validate-reset-token', [\App\Core\Http\Controllers\Auth\PasswordResetController::class, 'validateToken']);
    Route::post('/reset-password', [\App\Core\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword']);

    // Two-Factor Authentication (public routes for login verification)
    Route::post('/2fa/verify', [\App\Core\Http\Controllers\Auth\TwoFactorAuthController::class, 'verify']);

    // OAuth Routes
    Route::get('/oauth/providers', [\App\Core\Http\Controllers\Auth\OAuthController::class, 'providers']);
    Route::get('/oauth/{provider}/redirect', [\App\Core\Http\Controllers\Auth\OAuthController::class, 'redirect']);
    Route::get('/oauth/{provider}/callback', [\App\Core\Http\Controllers\Auth\OAuthController::class, 'callback']);
});

// Frontend error logging (rate limited to prevent log flooding)
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/log-frontend-error', [\App\Core\Http\Controllers\FrontendErrorController::class, 'log']);
    Route::post('/log-api-error', [\App\Core\Http\Controllers\FrontendErrorController::class, 'logApiError']);
    Route::post('/log-vue-error', [\App\Core\Http\Controllers\FrontendErrorController::class, 'logVueError']);
});

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // Two-Factor Authentication (authenticated routes)
    Route::prefix('2fa')->controller(\App\Core\Http\Controllers\Auth\TwoFactorAuthController::class)->group(function () {
        Route::get('/status', 'status');
        Route::post('/setup', 'setup');
        Route::post('/confirm', 'confirm');
        Route::post('/disable', 'disable');
        Route::post('/recovery-codes', 'recoveryCodes');
        Route::post('/regenerate-recovery-codes', 'regenerateRecoveryCodes');
    });

    // OAuth Account Linking (authenticated routes)
    Route::prefix('oauth')->controller(\App\Core\Http\Controllers\Auth\OAuthController::class)->group(function () {
        Route::get('/linked', 'linked');
        Route::get('/{provider}/link', 'link');
        Route::delete('/{provider}/unlink', 'unlink');
    });

    // WebSocket Routes
    Route::prefix('ws')->controller(\App\Core\Http\Controllers\WebSocketController::class)->group(function () {
        Route::post('/auth', 'authorize');
        Route::post('/poll', 'poll');
        Route::get('/online-count', 'onlineCount');
        Route::post('/heartbeat', 'heartbeat');
        Route::get('/presence/{channel}', 'presenceMembers');
    });

    // Tickets (User Support)
    Route::prefix('tickets')->controller(\App\Plugins\Tickets\Controllers\TicketsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/categories', 'categories');
        Route::post('/', 'store');
        Route::get('/unread-count', 'unreadCount');
        Route::get('/{id}', 'show');
        Route::post('/{id}/reply', 'reply');
        Route::post('/{id}/close', 'close');
    });

    // Admin Panel Routes (require admin role/permission)
    Route::prefix('admin')->name('admin.')->middleware('role:admin|moderator')->group(function () {

        // Dashboard Statistics
        Route::get('/stats', [\App\Core\Http\Controllers\Admin\DashboardStatsController::class, 'index']);

        // Plugin Management
        Route::prefix('plugins')->controller(PluginController::class)->group(function () {
            // List plugins/themes
            Route::get('/', 'index');

            // Upload plugin/theme ZIP
            Route::post('/upload', 'upload');

            // Create new plugin structure
            Route::post('/create', 'create');

            // Plugin operations
            Route::post('/{slug}/install', 'install');
            Route::delete('/{slug}', 'uninstall');
            Route::put('/{slug}/enable', 'enable');
            Route::put('/{slug}/disable', 'disable');
            Route::put('/{slug}/reactivate', 'reactivate');
            Route::delete('/{slug}/staging', 'removeStaging');

            // Theme operations
            Route::post('/{slug}/install-theme', 'installTheme');
            Route::put('/{slug}/activate-theme', 'activateTheme');

            // Get themes list
            Route::get('/themes', 'index')->defaults('type', 'theme');
        });

        // User Management
        Route::prefix('users')->controller(\App\Core\Http\Controllers\Admin\UserManagementController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/statistics', 'statistics');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/{id}/ban', 'ban');
            Route::post('/{id}/unban', 'unban');
        });

        // Roles & Permissions
        Route::prefix('roles')->controller(\App\Core\Http\Controllers\Admin\RolePermissionController::class)->group(function () {
            Route::get('/', 'indexRoles');
            Route::post('/', 'storeRole');
            Route::patch('/{id}', 'updateRole');
            Route::delete('/{id}', 'destroyRole');
        });
        Route::get('/permissions', [\App\Core\Http\Controllers\Admin\RolePermissionController::class, 'indexPermissions']);
        Route::post('/users/{id}/roles', [\App\Core\Http\Controllers\Admin\RolePermissionController::class, 'assignRoleToUser']);
        Route::delete('/users/{id}/roles', [\App\Core\Http\Controllers\Admin\RolePermissionController::class, 'removeRoleFromUser']);

        // Settings
        Route::prefix('settings')->controller(\App\Core\Http\Controllers\Admin\SettingsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::patch('/', 'update');
            Route::get('/{key}', 'show');
            Route::delete('/{key}', 'destroy');
        });

        // Webhooks Management
        Route::prefix('webhooks')->controller(\App\Core\Http\Controllers\Admin\WebhookController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/events', 'events');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/{id}/toggle', 'toggle');
            Route::post('/{id}/test', 'test');
            Route::get('/{id}/deliveries', 'deliveries');
            Route::post('/{id}/deliveries/{deliveryId}/retry', 'retryDelivery');
            Route::post('/{id}/regenerate-secret', 'regenerateSecret');
        });

        // Email Settings & Templates
        Route::prefix('email')->controller(\App\Core\Http\Controllers\Admin\EmailSettingsController::class)->group(function () {
            Route::get('/settings', 'getSettings');
            Route::post('/settings', 'updateSettings');
            Route::post('/settings/test', 'testSettings');
            Route::post('/send', 'sendManualEmail'); // Manual email compose
            Route::get('/templates', 'getTemplates');
            Route::post('/templates', 'createTemplate');
            Route::post('/templates/seed-defaults', 'seedDefaultTemplates'); // Must be before {id} routes
            Route::get('/templates/{id}', 'getTemplate');
            Route::patch('/templates/{id}', 'updateTemplate');
            Route::delete('/templates/{id}', 'deleteTemplate');
            Route::post('/templates/{id}/preview', 'previewTemplate');
            Route::post('/templates/{id}/test', 'sendTestTemplate');
        });

        // Game Configuration
        Route::apiResource('locations', \App\Core\Http\Controllers\Admin\LocationController::class);
        Route::apiResource('ranks', \App\Core\Http\Controllers\Admin\RankController::class);
        Route::apiResource('memberships', \App\Core\Http\Controllers\Admin\MembershipController::class);

        // Crime System
        Route::apiResource('crimes', \App\Plugins\Crimes\Controllers\CrimeManagementController::class);
        Route::apiResource('organized-crimes', \App\Plugins\OrganizedCrime\Controllers\OrganizedCrimeController::class);

        // Employment System Management
        Route::prefix('employment')->group(function () {
            Route::get('/employees', [\App\Plugins\Employment\Controllers\EmploymentController::class, 'allEmployees']);
            Route::get('/statistics', [\App\Plugins\Employment\Controllers\EmploymentController::class, 'statistics']);
            Route::apiResource('companies', \App\Plugins\Employment\Controllers\CompanyController::class);
            Route::apiResource('positions', \App\Plugins\Employment\Controllers\PositionController::class);
        });

        // Education System Management
        Route::prefix('education')->group(function () {
            Route::get('/enrollments', [\App\Plugins\Education\Controllers\EducationController::class, 'allEnrollments']);
            Route::get('/statistics', [\App\Plugins\Education\Controllers\EducationController::class, 'statistics']);
            Route::apiResource('courses', \App\Plugins\Education\Controllers\CourseController::class);
        });

        // Stock Market Management
        Route::prefix('stocks')->group(function () {
            Route::get('/transactions', [\App\Plugins\Stocks\Controllers\StockController::class, 'allTransactions']);
            Route::get('/statistics', [\App\Plugins\Stocks\Controllers\StockController::class, 'statistics']);
            Route::post('/{id}/update-price', [\App\Plugins\Stocks\Controllers\StockController::class, 'updatePrice']);
            Route::apiResource('stocks', \App\Plugins\Stocks\Controllers\StockController::class);
        });

        // Casino Management
        Route::prefix('casino')->group(function () {
            Route::get('/bets', [\App\Plugins\Casino\Controllers\CasinoController::class, 'allBets']);
            Route::get('/statistics', [\App\Plugins\Casino\Controllers\CasinoController::class, 'statistics']);
            Route::post('/lotteries/{id}/draw', [\App\Plugins\Casino\Controllers\LotteryController::class, 'drawWinner']);
            Route::apiResource('games', \App\Plugins\Casino\Controllers\CasinoGameController::class);
            Route::apiResource('lotteries', \App\Plugins\Casino\Controllers\LotteryController::class);
        });
        Route::get('crime-attempts', [\App\Plugins\Crimes\Controllers\CrimeAttemptController::class, 'index']);

        // Economy
        Route::apiResource('drugs', \App\Plugins\Drugs\Controllers\DrugManagementController::class);
        Route::apiResource('items', \App\Plugins\Inventory\Controllers\ItemManagementController::class);
        Route::apiResource('properties', \App\Plugins\Properties\Controllers\PropertyManagementController::class);
        Route::apiResource('cars', \App\Plugins\Racing\Controllers\CarManagementController::class);

        // Combat & Racing
        Route::apiResource('theft-types', \App\Plugins\Theft\Controllers\TheftTypeController::class);
        Route::apiResource('bounties', \App\Plugins\Bounty\Controllers\BountyManagementController::class);
        Route::get('combat-logs', [\App\Plugins\Combat\Controllers\CombatLogController::class, 'index']);
        Route::get('races', [\App\Plugins\Racing\Controllers\RaceManagementController::class, 'index']);

        // Combat NPC System Management
        Route::prefix('combat-locations')->controller(\App\Plugins\Combat\Controllers\CombatManagementController::class)->group(function () {
            Route::get('/', 'getLocations');
            Route::post('/', 'createLocation');
            Route::match(['put', 'patch'], '/{id}', 'updateLocation');
            Route::delete('/{id}', 'deleteLocation');
        });
        Route::prefix('combat-areas')->controller(\App\Plugins\Combat\Controllers\CombatManagementController::class)->group(function () {
            Route::get('/', 'getAreas');
            Route::post('/', 'createArea');
            Route::match(['put', 'patch'], '/{id}', 'updateArea');
            Route::delete('/{id}', 'deleteArea');
        });
        Route::prefix('combat-enemies')->controller(\App\Plugins\Combat\Controllers\CombatManagementController::class)->group(function () {
            Route::get('/', 'getEnemies');
            Route::post('/', 'createEnemy');
            Route::match(['put', 'patch'], '/{id}', 'updateEnemy');
            Route::delete('/{id}', 'deleteEnemy');
        });

        // Social Features
        Route::apiResource('gangs', \App\Plugins\Gang\Controllers\GangManagementController::class);
        Route::get('gang-logs', [\App\Plugins\Gang\Controllers\GangLogController::class, 'index']);
        Route::apiResource('chat-channels', \App\Plugins\Chat\Controllers\ChatChannelManagementController::class);

        // Progression
        Route::apiResource('missions', \App\Plugins\Missions\Controllers\MissionManagementController::class);
        // Route::apiResource('achievements', \App\Plugins\Achievements\Controllers\AchievementManagementController::class); // Disabled - plugin not installed
        Route::apiResource('daily-rewards', \App\Plugins\DailyRewards\Controllers\DailyRewardController::class);

        // Content Management
        Route::prefix('content')->group(function () {
            Route::apiResource('faq-categories', \App\Plugins\Wiki\Controllers\FaqCategoryController::class);
            Route::apiResource('faqs', \App\Plugins\Wiki\Controllers\FaqController::class);
            Route::apiResource('wiki-categories', \App\Plugins\Wiki\Controllers\WikiCategoryController::class);
            Route::apiResource('wiki-pages', \App\Plugins\Wiki\Controllers\WikiPageController::class);
            Route::apiResource('announcements', \App\Plugins\Announcements\Controllers\AnnouncementController::class);
            Route::apiResource('forum-categories', \App\Plugins\Forum\Controllers\ForumCategoryController::class);
        });

        // Support System
        Route::prefix('support')->group(function () {
            Route::apiResource('ticket-categories', \App\Plugins\Tickets\Controllers\TicketCategoryController::class);
            Route::get('tickets', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'index']);
            Route::get('tickets/staff/users', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'getStaffUsers']);
            Route::get('tickets/{id}', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'show']);
            Route::patch('tickets/{id}', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'update']);
            Route::delete('tickets/{id}', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'destroy']);
            Route::post('tickets/{id}/reply', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'reply']);
            Route::patch('tickets/{id}/status', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'updateStatus']);
            Route::patch('tickets/{id}/assign', [\App\Plugins\Tickets\Controllers\TicketManagementController::class, 'assign']);
        });

        // System Administration
        Route::get('error-logs', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'index']);
        Route::get('error-logs/statistics', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'statistics']);
        Route::get('error-logs/laravel-log', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'laravelLog']);
        Route::post('error-logs/laravel-log/sync', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'syncLaravelLog']);
        Route::delete('error-logs/laravel-log/clear', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'clearLaravelLog']);
        Route::delete('error-logs/clear', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'clearAll']);
        Route::get('error-logs/{id}', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'show']);
        Route::patch('error-logs/{id}/resolve', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'resolve']);

        // Admin Notifications
        Route::prefix('notifications')->controller(\App\Core\Http\Controllers\Admin\AdminNotificationController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/recent', 'recent');
            Route::get('/unread-count', 'unreadCount');
            Route::post('/{id}/read', 'markAsRead');
            Route::post('/read-all', 'markAllAsRead');
            Route::delete('/{id}', 'destroy');
            Route::delete('/clear-read', 'clearRead');
            Route::post('/test', 'sendTest');
            Route::post('/broadcast', 'broadcast');
        });
        Route::patch('error-logs/{id}/unresolve', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'unresolve']);
        Route::delete('error-logs/{id}', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'destroy']);
        Route::post('error-logs/bulk-resolve', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'bulkResolve']);
        Route::post('error-logs/bulk-delete', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'bulkDelete']);
        Route::delete('error-logs/resolved/all', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'deleteResolved']);
        Route::delete('error-logs/old', [\App\Core\Http\Controllers\Admin\ErrorLogController::class, 'deleteOld']);
        Route::apiResource('ip-bans', \App\Core\Http\Controllers\Admin\IpBanController::class);
        Route::get('user-timers', [\App\Core\Http\Controllers\Admin\UserTimerController::class, 'index']);

        // User Tools (Admin)
        Route::prefix('user-tools')->controller(\App\Core\Http\Controllers\Admin\UserToolsController::class)->group(function () {
            Route::get('/search', 'search');
            Route::get('/{id}', 'show');
            Route::get('/{id}/inventory', 'inventory');
            Route::get('/{id}/timers', 'timers');
            Route::delete('/{id}/timers/{timerType}', 'clearTimer');
            Route::get('/{id}/activity', 'activity');
            Route::get('/{id}/flags', 'flags');
            Route::post('/{id}/flags', 'addFlag');
            Route::delete('/{id}/flags/{flagType}', 'removeFlag');
            Route::get('/{id}/jobs', 'jobs');
            Route::get('/{id}/job-history', 'jobHistory');
        });

        // Activity Logs (Admin)
        Route::prefix('activity')->controller(\App\Core\Http\Controllers\Admin\ActivityLogController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/recent', 'recent');
            Route::get('/user/{userId}', 'userActivity');
            Route::get('/suspicious', 'suspicious');
            Route::post('/clean', 'clean');
        });

        // Cache Management
        Route::prefix('cache')->controller(\App\Core\Http\Controllers\Admin\CacheController::class)->group(function () {
            Route::post('/clear', 'clear');
            Route::post('/clear-user/{userId}', 'clearUser');
            Route::post('/warm-up', 'warmUp');
        });

        // Staff Chat
        Route::prefix('staff-chat')->controller(\App\Core\Http\Controllers\Admin\StaffChatController::class)->group(function () {
            Route::get('/messages', 'messages');
            Route::post('/messages', 'send');
            Route::get('/unread', 'unread');
        });

        // Item Market Management
        Route::prefix('item-market')->controller(\App\Plugins\Inventory\Controllers\ItemMarketController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/listings', 'listings');
            Route::get('/transactions', 'transactions');
            Route::get('/price-history/{itemId}', 'priceHistory');
            Route::post('/listings/{id}/cancel', 'cancelListing');
            Route::delete('/listings/{id}', 'deleteListing');
            Route::get('/points-market', 'pointsMarket');
        });

        // Backup Management
        Route::prefix('backups')->controller(\App\Http\Controllers\Admin\BackupController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/settings', 'settings');
            Route::put('/settings', 'updateSettings');
            Route::put('/storage', 'updateStorage');
            Route::post('/test-storage', 'testStorage');
            Route::post('/', 'store');
            Route::get('/{id}/download', 'download');
            Route::post('/{id}/restore', 'restore');
            Route::delete('/{id}', 'destroy');
        });

        // System Health Monitoring
        Route::prefix('system')->controller(\App\Http\Controllers\Admin\SystemHealthController::class)->group(function () {
            Route::get('/health', 'index');
            Route::post('/queue/retry-failed', 'retryFailedJobs');
            Route::post('/cache/clear', 'clearCache');
        });

        // API Keys Management
        Route::prefix('api-keys')->controller(\App\Core\Http\Controllers\Admin\ApiKeyController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/permissions', 'permissions');
            Route::get('/analytics', 'analytics');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/{id}/toggle', 'toggle');
            Route::post('/{id}/regenerate-secret', 'regenerateSecret');
            Route::get('/{id}/logs', 'logs');
        });
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Chat Channels
    Route::get('/channels', [ChatChannelController::class, 'index']);
    Route::get('/channels/unread-count', [ChatChannelController::class, 'unreadCount']);
    Route::get('/unread-count', [ChatChannelController::class, 'unreadCount']); // Alias for frontend compatibility
    Route::get('/chat/unread-count', [ChatChannelController::class, 'unreadCount']); // Frontend expects /chat prefix
    Route::post('/channels', [ChatChannelController::class, 'store']);
    Route::get('/channels/{channel}', [ChatChannelController::class, 'show']);
    Route::patch('/channels/{channel}', [ChatChannelController::class, 'update']);
    Route::delete('/channels/{channel}', [ChatChannelController::class, 'destroy']);
    Route::post('/channels/{channel}/members', [ChatChannelController::class, 'addMember']);
    Route::delete('/channels/{channel}/members/{userId}', [ChatChannelController::class, 'removeMember']);

    // Chat Messages
    Route::get('/channels/{channel}/messages', [ChatMessageController::class, 'index']);
    Route::get('/channels/{channel}/pinned', [ChatMessageController::class, 'pinnedMessages']);
    Route::post('/channels/{channel}/messages', [ChatMessageController::class, 'store']);
    Route::patch('/messages/{message}', [ChatMessageController::class, 'update']);
    Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy']);
    Route::post('/messages/{message}/reactions', [ChatMessageController::class, 'addReaction']);
    Route::get('/messages/{message}/reactions', [ChatMessageController::class, 'reactions']);
    Route::post('/messages/{message}/pin', [ChatMessageController::class, 'pin']);
    Route::delete('/messages/{message}/pin', [ChatMessageController::class, 'unpin']);

    // Emoji Routes
    Route::get('/emojis', [EmojiController::class, 'index']);
    Route::get('/emojis/quick-reactions', [EmojiController::class, 'quickReactions']);
    Route::get('/emojis/search', [EmojiController::class, 'search']);

    // Text Formatter Routes (BBCode & JoyPixels)
    Route::post('/format/preview', [\App\Core\Http\Controllers\TextFormatterController::class, 'preview']);
    Route::get('/format/bbcodes', [\App\Core\Http\Controllers\TextFormatterController::class, 'bbcodes']);
    Route::get('/format/emojis', [\App\Core\Http\Controllers\TextFormatterController::class, 'emojis']);
    Route::post('/format/plain', [\App\Core\Http\Controllers\TextFormatterController::class, 'plain']);
    Route::get('/format/emoji/search', [\App\Core\Http\Controllers\TextFormatterController::class, 'searchEmoji']);

    // Direct Messages
    Route::get('/direct-messages', [DirectMessageController::class, 'index']);
    Route::get('/direct-messages/{user}', [DirectMessageController::class, 'show']);
    Route::post('/direct-messages', [DirectMessageController::class, 'store']);
    Route::delete('/direct-messages/{message}', [DirectMessageController::class, 'destroy']);
    Route::get('/direct-messages/unread-count', [DirectMessageController::class, 'unreadCount']);
    Route::patch('/direct-messages/{user}/read', [DirectMessageController::class, 'markAsRead']);

    // Support Tickets (User-facing)
    Route::prefix('tickets')->controller(\App\Plugins\Tickets\Controllers\TicketsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/categories', 'categories');
        Route::get('/unread-count', 'unreadCount');
        Route::get('/{id}', 'show');
        Route::post('/{id}/reply', 'reply');
        Route::post('/{id}/close', 'close');
    });

    // Game Core Routes
    Route::get('/dashboard', [\App\Core\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/player/{id}', [\App\Core\Http\Controllers\ProfileController::class, 'show']);

    // Announcements (public for logged-in users)
    Route::get('/announcements', [\App\Plugins\Announcements\Controllers\AnnouncementController::class, 'index']);
    Route::post('/announcements/{announcement}/view', [\App\Plugins\Announcements\Controllers\AnnouncementController::class, 'markViewed']);

    // Shop (alias for inventory/shop)
    Route::get('/shop', [\App\Plugins\Inventory\Controllers\InventoryController::class, 'shop']);

    // Player Statistics
    Route::prefix('stats')->controller(\App\Core\Http\Controllers\PlayerStatsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/player/{userId}', 'show');
        Route::post('/refresh', 'refresh');
    });

    // Activity Logs (Player's own)
    Route::get('/activity', [\App\Core\Http\Controllers\ActivityController::class, 'myActivity']);
    Route::get('/activity/my-activity', [\App\Core\Http\Controllers\ActivityController::class, 'myActivity']); // Alias for frontend compatibility

    // Notifications
    Route::prefix('notifications')->controller(\App\Core\Http\Controllers\NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/recent', 'recent');
        Route::get('/unread-count', 'unreadCount');
        Route::post('/{id}/read', 'markAsRead');
        Route::post('/mark-all-read', 'markAllAsRead');
        Route::delete('/{id}', 'delete');
        Route::delete('/read/clear', 'deleteRead');
    });

    // Travel
    Route::prefix('travel')->controller(\App\Plugins\Travel\Controllers\TravelController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{location}', 'travel');
    });

    // Crimes
    Route::prefix('crimes')->name('api.crimes.')->controller(\App\Plugins\Crimes\Controllers\CrimeController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/stats', 'stats')->name('stats');
        Route::post('/attempt', 'attempt')->name('attempt');
    });

    // Crime Locations
    Route::prefix('crime-locations')->name('api.crime-locations.')->controller(\App\Plugins\Crimes\Controllers\CrimeLocationsController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/attempt', 'attempt')->name('attempt');
        Route::get('/{id}/stats', 'stats')->name('stats');
    });

    // Gym
    Route::prefix('gym')->controller(\App\Plugins\Gym\Controllers\GymController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/train', 'train');
    });

    // Hospital
    Route::prefix('hospital')->controller(\App\Plugins\Hospital\Controllers\HospitalController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/heal', 'heal');
        Route::post('/heal-full', 'healFull');
    });

    // Bank
    Route::prefix('bank')->controller(\App\Plugins\Bank\Controllers\BankController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/deposit', 'deposit');
        Route::post('/withdraw', 'withdraw');
        Route::post('/transfer', 'transfer');
    });

    // Drugs
    Route::prefix('drugs')->controller(\App\Plugins\Drugs\Controllers\DrugController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/buy', 'buy');
        Route::post('/sell', 'sell');
    });

    // Jail
    Route::prefix('jail')->controller(\App\Plugins\Jail\Controllers\JailController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{target}/bustout', 'bustOut');
        Route::post('/bailout', 'bailOut');
    });
    // Alias for frontend compatibility
    Route::prefix('plugins/jail')->controller(\App\Plugins\Jail\Controllers\JailController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/bustout/{target}', 'bustOut');
        Route::post('/bailout', 'bailOut');
    });

    // Inventory
    Route::prefix('inventory')->controller(\App\Plugins\Inventory\Controllers\InventoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/shop', 'shop');
        Route::post('/buy/{item}', 'buy');
        Route::post('/sell/{inventoryId}', 'sell');
        Route::post('/equip/{inventoryId}', 'equip');
        Route::post('/unequip/{inventoryId}', 'unequip');
        Route::post('/use/{inventoryId}', 'use');
    });
    // Alias for frontend compatibility
    Route::prefix('plugins/inventory')->controller(\App\Plugins\Inventory\Controllers\InventoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/shop', 'shop');
        Route::post('/buy/{item}', 'buy');
        Route::post('/sell/{inventoryId}', 'sell');
        Route::post('/equip/{inventoryId}', 'equip');
        Route::post('/unequip/{inventoryId}', 'unequip');
        Route::post('/use/{inventoryId}', 'use');
    });

    // Combat
    Route::prefix('combat')->controller(\App\Plugins\Combat\Controllers\CombatController::class)->group(function () {
        // NPC Combat (new system)
        Route::get('/locations', 'locations');
        Route::post('/hunt', 'hunt');
        Route::post('/attack-npc', 'attackNPC');
        Route::post('/auto-attack-npc', 'autoAttackNPC');

        // PvP Combat (old system)
        Route::get('/', 'index');
        Route::post('/attack', 'attack');
    });

    // Theft
    Route::prefix('theft')->controller(\App\Plugins\Theft\Controllers\TheftController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/attempt/{theftType}', 'attempt');
        Route::get('/garage', 'garage');
        Route::post('/garage/{garageId}/sell', 'sell');
    });
    // Alias for frontend compatibility
    Route::prefix('plugins/theft')->controller(\App\Plugins\Theft\Controllers\TheftController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/attempt/{theftType}', 'attempt');
        Route::get('/garage', 'garage');
        Route::post('/garage/{garageId}/sell', 'sell');
    });

    // Racing
    Route::prefix('racing')->controller(\App\Plugins\Racing\Controllers\RaceController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/join/{race}', 'join');
        Route::post('/leave/{race}', 'leave');
        Route::post('/start/{race}', 'start');
    });
    // Alias for frontend compatibility
    Route::prefix('plugins/racing')->controller(\App\Plugins\Racing\Controllers\RaceController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/join/{race}', 'join');
        Route::post('/leave/{race}', 'leave');
        Route::post('/start/{race}', 'start');
    });

    // Properties
    Route::prefix('properties')->controller(\App\Plugins\Properties\Controllers\PropertyController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{property}/buy', 'buy');
        Route::post('/{property}/sell', 'sell');
        Route::post('/collect', 'collect');
    });

    // Bounties
    Route::prefix('bounties')->controller(\App\Plugins\Bounty\Controllers\BountyController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/place', 'place');
    });

    // Missions
    Route::prefix('missions')->controller(\App\Plugins\Missions\Controllers\MissionController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/start', 'start');
    });

    // Detective
    Route::prefix('detective')->controller(\App\Plugins\Detective\Controllers\DetectiveController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/hire', 'hire');
    });

    // Bullets
    Route::prefix('bullets')->controller(\App\Plugins\Bullets\Controllers\BulletController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/buy', 'buy');
    });

    // Gangs
    Route::prefix('gangs')->controller(\App\Plugins\Gang\Controllers\GangController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/leave', 'leave');
        Route::post('/kick/{playerId}', 'kick');
        Route::post('/deposit', 'deposit');
        Route::post('/withdraw', 'withdraw');
    });

    // Achievements
    Route::get('/achievements', [\App\Plugins\Achievements\Controllers\AchievementsController::class, 'index']);

    // Leaderboards
    Route::get('/leaderboards', [\App\Plugins\Leaderboards\Controllers\LeaderboardsController::class, 'index']);
    Route::prefix('plugins/leaderboards')->controller(\App\Plugins\Leaderboards\Controllers\LeaderboardsController::class)->group(function () {
        Route::get('/', 'index');
    });

    // Forum
    Route::prefix('forum')->controller(\App\Plugins\Forum\Controllers\ForumController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/category/{category}', 'category');
        Route::get('/topic/{topic}', 'topic');
        Route::post('/category/{category}/topic', 'createTopic');
        Route::post('/topic/{topic}/reply', 'reply');
    });
    Route::prefix('plugins/forum')->controller(\App\Plugins\Forum\Controllers\ForumController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/category/{category}', 'category');
        Route::get('/topic/{topic}', 'topic');
        Route::post('/category/{category}/topic', 'createTopic');
        Route::post('/topic/{topic}/reply', 'reply');
    });

    // Organized Crime
    Route::prefix('organized-crime')->controller(\App\Plugins\OrganizedCrime\Controllers\OrganizedCrimeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{crime}/attempt', 'attempt');
    });
    Route::prefix('plugins/organized-crime')->controller(\App\Plugins\OrganizedCrime\Controllers\OrganizedCrimeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{crime}/attempt', 'attempt');
    });

    // Employment/Jobs System
    Route::prefix('employment')->controller(\App\Plugins\Employment\Controllers\EmploymentController::class)->group(function () {
        Route::get('/positions', 'index');
        Route::get('/current', 'currentJob');
        Route::post('/apply', 'apply');
        Route::post('/work', 'work');
        Route::post('/quit', 'quit');
    });

    // Education System
    Route::prefix('education')->controller(\App\Plugins\Education\Controllers\EducationController::class)->group(function () {
        Route::get('/courses', 'index');
        Route::post('/enroll', 'enroll');
        Route::get('/progress', 'progress');
        Route::get('/history', 'history');
    });

    // Stock Market System
    Route::prefix('stocks')->controller(\App\Plugins\Stocks\Controllers\StockController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/portfolio/my', 'portfolio');
        Route::get('/{id}', 'show');
        Route::post('/buy', 'buy');
        Route::post('/sell', 'sell');
    });

    // Casino System
    Route::prefix('casino')->controller(\App\Plugins\Casino\Controllers\CasinoController::class)->group(function () {
        Route::get('/games', 'games');
        Route::post('/play/slots', 'playSlots');
        Route::post('/play/roulette', 'playRoulette');
        Route::post('/play/dice', 'playDice');
        Route::get('/stats', 'stats');
        Route::get('/history', 'history');
        Route::post('/lottery/buy', 'buyLotteryTicket');
    });
});

