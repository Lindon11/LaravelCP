<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatChannelController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\DirectMessageController;
use App\Http\Controllers\Api\EmojiController;
use App\Http\Controllers\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Frontend error logging (public - called from browser)
Route::post('/log-frontend-error', [\App\Http\Controllers\Api\FrontendErrorController::class, 'log']);
Route::post('/log-api-error', [\App\Http\Controllers\Api\FrontendErrorController::class, 'logApiError']);
Route::post('/log-vue-error', [\App\Http\Controllers\Api\FrontendErrorController::class, 'logVueError']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Admin Panel Routes (require admin role/permission)
    Route::prefix('admin')->name('admin.')->middleware('role:admin|moderator')->group(function () {

        // Dashboard Statistics
        Route::get('/stats', [\App\Http\Controllers\Admin\DashboardStatsController::class, 'index']);

        // Module Management
        Route::prefix('modules')->controller(ModuleController::class)->group(function () {
            // List modules/themes
            Route::get('/', 'index');

            // Upload module/theme ZIP
            Route::post('/upload', 'upload');

            // Create new module structure
            Route::post('/create', 'create');

            // Module operations
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
        Route::prefix('users')->controller(\App\Http\Controllers\Admin\UserManagementController::class)->group(function () {
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
        Route::prefix('roles')->controller(\App\Http\Controllers\Admin\RolePermissionController::class)->group(function () {
            Route::get('/', 'indexRoles');
            Route::post('/', 'storeRole');
            Route::patch('/{id}', 'updateRole');
            Route::delete('/{id}', 'destroyRole');
        });
        Route::get('/permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'indexPermissions']);
        Route::post('/users/{id}/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assignRoleToUser']);
        Route::delete('/users/{id}/roles', [\App\Http\Controllers\Admin\RolePermissionController::class, 'removeRoleFromUser']);

        // Settings
        Route::prefix('settings')->controller(\App\Http\Controllers\Admin\SettingsController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::patch('/', 'update');
            Route::get('/{key}', 'show');
            Route::delete('/{key}', 'destroy');
        });

        // Game Configuration
        Route::apiResource('locations', \App\Http\Controllers\Admin\LocationController::class);
        Route::apiResource('ranks', \App\Http\Controllers\Admin\RankController::class);
        Route::apiResource('memberships', \App\Http\Controllers\Admin\MembershipController::class);

        // Crime System
        Route::apiResource('crimes', \App\Http\Controllers\Admin\CrimeManagementController::class);
        Route::apiResource('organized-crimes', \App\Http\Controllers\Admin\OrganizedCrimeController::class);

        // Employment System Management
        Route::prefix('employment')->group(function () {
            Route::get('/employees', [\App\Http\Controllers\Admin\EmploymentController::class, 'allEmployees']);
            Route::get('/statistics', [\App\Http\Controllers\Admin\EmploymentController::class, 'statistics']);
            Route::apiResource('companies', \App\Http\Controllers\Admin\CompanyController::class);
            Route::apiResource('positions', \App\Http\Controllers\Admin\PositionController::class);
        });

        // Education System Management
        Route::prefix('education')->group(function () {
            Route::get('/enrollments', [\App\Http\Controllers\Admin\EducationController::class, 'allEnrollments']);
            Route::get('/statistics', [\App\Http\Controllers\Admin\EducationController::class, 'statistics']);
            Route::apiResource('courses', \App\Http\Controllers\Admin\CourseController::class);
        });

        // Stock Market Management
        Route::prefix('stocks')->group(function () {
            Route::get('/transactions', [\App\Http\Controllers\Admin\StockController::class, 'allTransactions']);
            Route::get('/statistics', [\App\Http\Controllers\Admin\StockController::class, 'statistics']);
            Route::post('/{id}/update-price', [\App\Http\Controllers\Admin\StockController::class, 'updatePrice']);
            Route::apiResource('stocks', \App\Http\Controllers\Admin\StockController::class);
        });

        // Casino Management
        Route::prefix('casino')->group(function () {
            Route::get('/bets', [\App\Http\Controllers\Admin\CasinoController::class, 'allBets']);
            Route::get('/statistics', [\App\Http\Controllers\Admin\CasinoController::class, 'statistics']);
            Route::post('/lotteries/{id}/draw', [\App\Http\Controllers\Admin\LotteryController::class, 'drawWinner']);
            Route::apiResource('games', \App\Http\Controllers\Admin\CasinoGameController::class);
            Route::apiResource('lotteries', \App\Http\Controllers\Admin\LotteryController::class);
        });
        Route::get('crime-attempts', [\App\Http\Controllers\Admin\CrimeAttemptController::class, 'index']);

        // Economy
        Route::apiResource('drugs', \App\Http\Controllers\Admin\DrugManagementController::class);
        Route::apiResource('items', \App\Http\Controllers\Admin\ItemManagementController::class);
        Route::apiResource('properties', \App\Http\Controllers\Admin\PropertyManagementController::class);
        Route::apiResource('cars', \App\Http\Controllers\Admin\CarManagementController::class);

        // Combat & Racing
        Route::apiResource('theft-types', \App\Http\Controllers\Admin\TheftTypeController::class);
        Route::apiResource('bounties', \App\Http\Controllers\Admin\BountyManagementController::class);
        Route::get('combat-logs', [\App\Http\Controllers\Admin\CombatLogController::class, 'index']);
        Route::get('races', [\App\Http\Controllers\Admin\RaceManagementController::class, 'index']);

        // Combat NPC System Management
        Route::prefix('combat-locations')->controller(\App\Http\Controllers\Admin\CombatManagementController::class)->group(function () {
            Route::get('/', 'getLocations');
            Route::post('/', 'createLocation');
            Route::match(['put', 'patch'], '/{id}', 'updateLocation');
            Route::delete('/{id}', 'deleteLocation');
        });
        Route::prefix('combat-areas')->controller(\App\Http\Controllers\Admin\CombatManagementController::class)->group(function () {
            Route::get('/', 'getAreas');
            Route::post('/', 'createArea');
            Route::match(['put', 'patch'], '/{id}', 'updateArea');
            Route::delete('/{id}', 'deleteArea');
        });
        Route::prefix('combat-enemies')->controller(\App\Http\Controllers\Admin\CombatManagementController::class)->group(function () {
            Route::get('/', 'getEnemies');
            Route::post('/', 'createEnemy');
            Route::match(['put', 'patch'], '/{id}', 'updateEnemy');
            Route::delete('/{id}', 'deleteEnemy');
        });

        // Social Features
        Route::apiResource('gangs', \App\Http\Controllers\Admin\GangManagementController::class);
        Route::get('gang-logs', [\App\Http\Controllers\Admin\GangLogController::class, 'index']);
        Route::apiResource('chat-channels', \App\Http\Controllers\Admin\ChatChannelManagementController::class);

        // Progression
        Route::apiResource('missions', \App\Http\Controllers\Admin\MissionManagementController::class);
        Route::apiResource('achievements', \App\Http\Controllers\Admin\AchievementManagementController::class);
        Route::apiResource('daily-rewards', \App\Http\Controllers\Admin\DailyRewardController::class);

        // Content Management
        Route::prefix('content')->group(function () {
            Route::apiResource('faq-categories', \App\Http\Controllers\Admin\FaqCategoryController::class);
            Route::apiResource('faqs', \App\Http\Controllers\Admin\FaqController::class);
            Route::apiResource('wiki-categories', \App\Http\Controllers\Admin\WikiCategoryController::class);
            Route::apiResource('wiki-pages', \App\Http\Controllers\Admin\WikiPageController::class);
            Route::apiResource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);
            Route::apiResource('forum-categories', \App\Http\Controllers\Admin\ForumCategoryController::class);
        });

        // Support System
        Route::prefix('support')->group(function () {
            Route::apiResource('ticket-categories', \App\Http\Controllers\Admin\TicketCategoryController::class);
            Route::get('tickets', [\App\Http\Controllers\Admin\TicketManagementController::class, 'index']);
            Route::get('tickets/{id}', [\App\Http\Controllers\Admin\TicketManagementController::class, 'show']);
            Route::patch('tickets/{id}', [\App\Http\Controllers\Admin\TicketManagementController::class, 'update']);
            Route::delete('tickets/{id}', [\App\Http\Controllers\Admin\TicketManagementController::class, 'destroy']);
            Route::post('tickets/{id}/reply', [\App\Http\Controllers\Admin\TicketManagementController::class, 'reply']);
            Route::patch('tickets/{id}/status', [\App\Http\Controllers\Admin\TicketManagementController::class, 'updateStatus']);
            Route::patch('tickets/{id}/assign', [\App\Http\Controllers\Admin\TicketManagementController::class, 'assign']);
        });

        // System Administration
        Route::get('error-logs', [\App\Http\Controllers\Admin\ErrorLogController::class, 'index']);
        Route::get('error-logs/statistics', [\App\Http\Controllers\Admin\ErrorLogController::class, 'statistics']);
        Route::get('error-logs/{id}', [\App\Http\Controllers\Admin\ErrorLogController::class, 'show']);
        Route::patch('error-logs/{id}/resolve', [\App\Http\Controllers\Admin\ErrorLogController::class, 'resolve']);

        // Admin Notifications
        Route::prefix('notifications')->controller(\App\Http\Controllers\Admin\AdminNotificationController::class)->group(function () {
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
        Route::patch('error-logs/{id}/unresolve', [\App\Http\Controllers\Admin\ErrorLogController::class, 'unresolve']);
        Route::delete('error-logs/{id}', [\App\Http\Controllers\Admin\ErrorLogController::class, 'destroy']);
        Route::post('error-logs/bulk-resolve', [\App\Http\Controllers\Admin\ErrorLogController::class, 'bulkResolve']);
        Route::post('error-logs/bulk-delete', [\App\Http\Controllers\Admin\ErrorLogController::class, 'bulkDelete']);
        Route::delete('error-logs/resolved/all', [\App\Http\Controllers\Admin\ErrorLogController::class, 'deleteResolved']);
        Route::delete('error-logs/old', [\App\Http\Controllers\Admin\ErrorLogController::class, 'deleteOld']);
        Route::apiResource('ip-bans', \App\Http\Controllers\Admin\IpBanController::class);
        Route::get('user-timers', [\App\Http\Controllers\Admin\UserTimerController::class, 'index']);

        // Activity Logs (Admin)
        Route::prefix('activity')->controller(\App\Http\Controllers\Admin\ActivityLogController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/recent', 'recent');
            Route::get('/user/{userId}', 'userActivity');
            Route::get('/suspicious', 'suspicious');
            Route::post('/clean', 'clean');
        });

        // Cache Management
        Route::prefix('cache')->controller(\App\Http\Controllers\Admin\CacheController::class)->group(function () {
            Route::post('/clear', 'clear');
            Route::post('/clear-user/{userId}', 'clearUser');
            Route::post('/warm-up', 'warmUp');
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

    // Direct Messages
    Route::get('/direct-messages', [DirectMessageController::class, 'index']);
    Route::get('/direct-messages/{user}', [DirectMessageController::class, 'show']);
    Route::post('/direct-messages', [DirectMessageController::class, 'store']);
    Route::delete('/direct-messages/{message}', [DirectMessageController::class, 'destroy']);
    Route::get('/direct-messages/unread-count', [DirectMessageController::class, 'unreadCount']);
    Route::patch('/direct-messages/{user}/read', [DirectMessageController::class, 'markAsRead']);

    // Support Tickets (User-facing)
    Route::prefix('tickets')->controller(\App\Http\Controllers\Api\TicketsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/categories', 'categories');
        Route::get('/unread-count', 'unreadCount');
        Route::get('/{id}', 'show');
        Route::post('/{id}/reply', 'reply');
        Route::post('/{id}/close', 'close');
    });

    // Game Core Routes
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/player/{id}', [\App\Http\Controllers\ProfileController::class, 'show']);

    // Announcements (public for logged-in users)
    Route::get('/announcements', [\App\Http\Controllers\Api\AnnouncementController::class, 'index']);
    Route::post('/announcements/{announcement}/view', [\App\Http\Controllers\Api\AnnouncementController::class, 'markViewed']);

    // Shop (alias for inventory/shop)
    Route::get('/shop', [\App\Http\Controllers\Api\InventoryController::class, 'shop']);

    // Player Statistics
    Route::prefix('stats')->controller(\App\Http\Controllers\Api\PlayerStatsController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/player/{userId}', 'show');
        Route::post('/refresh', 'refresh');
    });

    // Activity Logs (Player's own)
    Route::get('/activity', [\App\Http\Controllers\Api\ActivityController::class, 'myActivity']);
    Route::get('/activity/my-activity', [\App\Http\Controllers\Api\ActivityController::class, 'myActivity']); // Alias for frontend compatibility

    // Notifications
    Route::prefix('notifications')->controller(\App\Http\Controllers\NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/recent', 'recent');
        Route::get('/unread-count', 'unreadCount');
        Route::post('/{id}/read', 'markAsRead');
        Route::post('/mark-all-read', 'markAllAsRead');
        Route::delete('/{id}', 'delete');
        Route::delete('/read/clear', 'deleteRead');
    });

    // Travel
    Route::prefix('travel')->controller(\App\Http\Controllers\Api\TravelController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{location}', 'travel');
    });

    // Crimes
    Route::prefix('crimes')->name('api.crimes.')->controller(\App\Http\Controllers\Api\CrimesController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/stats', 'stats')->name('stats');
        Route::post('/attempt', 'attempt')->name('attempt');
    });

    // Crime Locations
    Route::prefix('crime-locations')->name('api.crime-locations.')->controller(\App\Http\Controllers\Api\CrimeLocationsController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/attempt', 'attempt')->name('attempt');
        Route::get('/{id}/stats', 'stats')->name('stats');
    });

    // Gym
    Route::prefix('gym')->controller(\App\Http\Controllers\Api\GymController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/train', 'train');
    });

    // Hospital
    Route::prefix('hospital')->controller(\App\Http\Controllers\Api\HospitalController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/heal', 'heal');
        Route::post('/heal-full', 'healFull');
    });

    // Bank
    Route::prefix('bank')->controller(\App\Http\Controllers\Api\BankController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/deposit', 'deposit');
        Route::post('/withdraw', 'withdraw');
        Route::post('/transfer', 'transfer');
    });

    // Drugs
    Route::prefix('drugs')->controller(\App\Http\Controllers\Api\DrugsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/buy', 'buy');
        Route::post('/sell', 'sell');
    });

    // Jail
    Route::prefix('jail')->controller(\App\Http\Controllers\Api\JailController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{target}/bustout', 'bustOut');
        Route::post('/bailout', 'bailOut');
    });
    // Alias for frontend compatibility
    Route::prefix('modules/jail')->controller(\App\Http\Controllers\Api\JailController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/bustout/{target}', 'bustOut');
        Route::post('/bailout', 'bailOut');
    });

    // Inventory
    Route::prefix('inventory')->controller(\App\Http\Controllers\Api\InventoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/shop', 'shop');
        Route::post('/buy/{item}', 'buy');
        Route::post('/sell/{inventoryId}', 'sell');
        Route::post('/equip/{inventoryId}', 'equip');
        Route::post('/unequip/{inventoryId}', 'unequip');
        Route::post('/use/{inventoryId}', 'use');
    });
    // Alias for frontend compatibility
    Route::prefix('modules/inventory')->controller(\App\Http\Controllers\Api\InventoryController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/shop', 'shop');
        Route::post('/buy/{item}', 'buy');
        Route::post('/sell/{inventoryId}', 'sell');
        Route::post('/equip/{inventoryId}', 'equip');
        Route::post('/unequip/{inventoryId}', 'unequip');
        Route::post('/use/{inventoryId}', 'use');
    });

    // Combat
    Route::prefix('combat')->controller(\App\Http\Controllers\Api\CombatController::class)->group(function () {
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
    Route::prefix('theft')->controller(\App\Http\Controllers\Api\TheftController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/attempt/{theftType}', 'attempt');
        Route::get('/garage', 'garage');
        Route::post('/garage/{garageId}/sell', 'sell');
    });
    // Alias for frontend compatibility
    Route::prefix('modules/theft')->controller(\App\Http\Controllers\Api\TheftController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/attempt/{theftType}', 'attempt');
        Route::get('/garage', 'garage');
        Route::post('/garage/{garageId}/sell', 'sell');
    });

    // Racing
    Route::prefix('racing')->controller(\App\Http\Controllers\Api\RacingController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/join/{race}', 'join');
        Route::post('/leave/{race}', 'leave');
        Route::post('/start/{race}', 'start');
    });
    // Alias for frontend compatibility
    Route::prefix('modules/racing')->controller(\App\Http\Controllers\Api\RacingController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/join/{race}', 'join');
        Route::post('/leave/{race}', 'leave');
        Route::post('/start/{race}', 'start');
    });

    // Properties
    Route::prefix('properties')->controller(\App\Http\Controllers\Api\PropertiesController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{property}/buy', 'buy');
        Route::post('/{property}/sell', 'sell');
        Route::post('/collect', 'collect');
    });

    // Bounties
    Route::prefix('bounties')->controller(\App\Http\Controllers\Api\BountyController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/place', 'place');
    });

    // Missions
    Route::prefix('missions')->controller(\App\Http\Controllers\Api\MissionsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/start', 'start');
    });

    // Detective
    Route::prefix('detective')->controller(\App\Http\Controllers\Api\DetectiveController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/hire', 'hire');
    });

    // Bullets
    Route::prefix('bullets')->controller(\App\Http\Controllers\Api\BulletsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/buy', 'buy');
    });

    // Gangs
    Route::prefix('gangs')->controller(\App\Http\Controllers\Api\GangsController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/create', 'create');
        Route::post('/leave', 'leave');
        Route::post('/kick/{playerId}', 'kick');
        Route::post('/deposit', 'deposit');
        Route::post('/withdraw', 'withdraw');
    });

    // Achievements
    Route::get('/achievements', [\App\Http\Controllers\Api\AchievementsController::class, 'index']);

    // Leaderboards
    Route::get('/leaderboards', [\App\Http\Controllers\Api\LeaderboardsController::class, 'index']);
    Route::prefix('modules/leaderboards')->controller(\App\Http\Controllers\Api\LeaderboardsController::class)->group(function () {
        Route::get('/', 'index');
    });

    // Forum
    Route::prefix('forum')->controller(\App\Http\Controllers\Api\ForumController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/category/{category}', 'category');
        Route::get('/topic/{topic}', 'topic');
        Route::post('/category/{category}/topic', 'createTopic');
        Route::post('/topic/{topic}/reply', 'reply');
    });
    Route::prefix('modules/forum')->controller(\App\Http\Controllers\Api\ForumController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/category/{category}', 'category');
        Route::get('/topic/{topic}', 'topic');
        Route::post('/category/{category}/topic', 'createTopic');
        Route::post('/topic/{topic}/reply', 'reply');
    });

    // Organized Crime
    Route::prefix('organized-crime')->controller(\App\Http\Controllers\Api\OrganizedCrimeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{crime}/attempt', 'attempt');
    });
    Route::prefix('modules/organized-crime')->controller(\App\Http\Controllers\Api\OrganizedCrimeController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{crime}/attempt', 'attempt');
    });

    // Employment/Jobs System
    Route::prefix('employment')->controller(\App\Http\Controllers\Api\EmploymentController::class)->group(function () {
        Route::get('/positions', 'index');
        Route::get('/current', 'currentJob');
        Route::post('/apply', 'apply');
        Route::post('/work', 'work');
        Route::post('/quit', 'quit');
    });

    // Education System
    Route::prefix('education')->controller(\App\Http\Controllers\Api\EducationController::class)->group(function () {
        Route::get('/courses', 'index');
        Route::post('/enroll', 'enroll');
        Route::get('/progress', 'progress');
        Route::get('/history', 'history');
    });

    // Stock Market System
    Route::prefix('stocks')->controller(\App\Http\Controllers\Api\StockMarketController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/portfolio/my', 'portfolio');
        Route::get('/{id}', 'show');
        Route::post('/buy', 'buy');
        Route::post('/sell', 'sell');
    });

    // Casino System
    Route::prefix('casino')->controller(\App\Http\Controllers\Api\CasinoController::class)->group(function () {
        Route::get('/games', 'games');
        Route::post('/play/slots', 'playSlots');
        Route::post('/play/roulette', 'playRoulette');
        Route::post('/play/dice', 'playDice');
        Route::get('/stats', 'stats');
        Route::get('/history', 'history');
        Route::post('/lottery/buy', 'buyLotteryTicket');
    });
});

