<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatChannelController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\DirectMessageController;
use App\Http\Controllers\Api\ModuleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    
    // Admin Panel Routes (require admin role/permission)
    Route::prefix('admin')->middleware('role:admin|moderator')->group(function () {
        
        // Module Management
        Route::prefix('modules')->controller(ModuleController::class)->group(function () {
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::patch('/{id}/toggle', 'toggle');
            Route::patch('/{id}', 'update');
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
            Route::post('tickets/{id}/reply', [\App\Http\Controllers\Admin\TicketManagementController::class, 'reply']);
            Route::patch('tickets/{id}/status', [\App\Http\Controllers\Admin\TicketManagementController::class, 'updateStatus']);
            Route::patch('tickets/{id}/assign', [\App\Http\Controllers\Admin\TicketManagementController::class, 'assign']);
        });

        // System Administration
        Route::get('error-logs', [\App\Http\Controllers\Admin\ErrorLogController::class, 'index']);
        Route::delete('error-logs/{id}', [\App\Http\Controllers\Admin\ErrorLogController::class, 'destroy']);
        Route::apiResource('ip-bans', \App\Http\Controllers\Admin\IpBanController::class);
        Route::get('user-timers', [\App\Http\Controllers\Admin\UserTimerController::class, 'index']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Chat Channels
    Route::get('/channels', [ChatChannelController::class, 'index']);
    Route::post('/channels', [ChatChannelController::class, 'store']);
    Route::get('/channels/{channel}', [ChatChannelController::class, 'show']);
    Route::patch('/channels/{channel}', [ChatChannelController::class, 'update']);
    Route::delete('/channels/{channel}', [ChatChannelController::class, 'destroy']);
    Route::post('/channels/{channel}/members', [ChatChannelController::class, 'addMember']);
    Route::delete('/channels/{channel}/members/{userId}', [ChatChannelController::class, 'removeMember']);

    // Chat Messages
    Route::get('/channels/{channel}/messages', [ChatMessageController::class, 'index']);
    Route::post('/channels/{channel}/messages', [ChatMessageController::class, 'store']);
    Route::patch('/messages/{message}', [ChatMessageController::class, 'update']);
    Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy']);
    Route::post('/messages/{message}/reactions', [ChatMessageController::class, 'addReaction']);
    Route::get('/messages/{message}/reactions', [ChatMessageController::class, 'reactions']);

    // Direct Messages
    Route::get('/direct-messages', [DirectMessageController::class, 'index']);
    Route::get('/direct-messages/{user}', [DirectMessageController::class, 'show']);
    Route::post('/direct-messages', [DirectMessageController::class, 'store']);
    Route::delete('/direct-messages/{message}', [DirectMessageController::class, 'destroy']);
    Route::get('/direct-messages/unread-count', [DirectMessageController::class, 'unreadCount']);
    Route::patch('/direct-messages/{user}/read', [DirectMessageController::class, 'markAsRead']);

    // Game Core Routes
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/player/{id}', [\App\Http\Controllers\ProfileController::class, 'show']);
    
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
    Route::prefix('crimes')->name('crimes.')->controller(\App\Http\Controllers\Api\CrimesController::class)->group(function () {
        Route::get('/', 'index')->name('list');
        Route::get('/stats', 'stats')->name('stats');
        Route::post('/attempt', 'attempt')->name('attempt');
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
});

