# Core System

The Core system (`app/Core/`) contains essential components that are always loaded and available to all plugins.

---

## Overview

```
app/Core/
├── Events/           # System events
├── Facades/          # Laravel facades
├── Helpers/          # Helper functions
├── Http/             # HTTP layer
│   ├── Controllers/  # API controllers
│   └── Middleware/   # HTTP middleware
├── Models/           # Core Eloquent models
├── Observers/        # Model observers
├── Providers/        # Service providers
└── Services/         # Business logic services
```

---

## Core Models

### User Model

The central user model (`app/Core/Models/User.php`):

```php
use App\Core\Models\User;

// Basic user attributes
$user->name;
$user->username;
$user->email;
$user->level;
$user->experience;

// Combat stats
$user->strength;
$user->defense;
$user->speed;
$user->health;
$user->max_health;

// Economy
$user->cash;
$user->bank;
$user->energy;
$user->max_energy;

// Position
$user->location_id;
$user->rank_id;

// Status
$user->jail_until;
$user->last_active;
$user->is_banned;
```

#### User Relationships

```php
// Location
$user->currentLocation;     // belongsTo Location
$user->location;            // Alias

// Rank
$user->currentRank;         // belongsTo Rank

// Memberships
$user->memberships;         // hasMany UserMembership
$user->activeMembership;    // Active VIP status

// Inventory
$user->inventory;           // hasMany UserItem
$user->hasItem($itemId);
$user->getItemCount($itemId);

// Properties
$user->properties;          // belongsToMany Property

// Gang
$user->gang;                // belongsTo Gang
$user->isGangLeader();
```

#### User Methods

```php
// Timers (cooldowns)
$user->hasTimer('crime');           // Check if on cooldown
$user->getTimer('crime');           // Get remaining seconds
$user->setTimer('crime', 60);       // Set 60 second cooldown
$user->clearTimer('crime');         // Clear cooldown

// Status checks
$user->isJailed();                  // In jail?
$user->isHospitalized();            // In hospital?
$user->canTravel();                 // Can travel?
$user->isOnline();                  // Recently active?

// Experience & leveling
$user->addExperience(100);          // Add XP (auto levels)
$user->expForNextLevel();           // XP needed for next level

// Economy
$user->canAfford($amount);          // Has cash?
$user->deductCash($amount);         // Remove cash
$user->addCash($amount);            // Add cash
$user->bankDeposit($amount);        // Deposit to bank
$user->bankWithdraw($amount);       // Withdraw from bank
```

### Setting Model

Game settings (`app/Core/Models/Setting.php`):

```php
use App\Core\Models\Setting;

// Get setting value
$value = Setting::get('game.name', 'Default Name');

// Set value
Setting::set('game.name', 'My Game');

// Get all settings in a group
$gameSettings = Setting::getGroup('game');

// Check if setting exists
Setting::has('custom.setting');
```

### Location Model

Game locations (`app/Core/Models/Location.php`):

```php
use App\Core\Models\Location;

// Get all locations
$locations = Location::active()->get();

// Get location by ID
$location = Location::find(1);

// Location properties
$location->name;
$location->description;
$location->travel_time;     // Minutes to travel here
$location->travel_cost;     // Cash to travel here
```

### Rank Model

Player ranks (`app/Core/Models/Rank.php`):

```php
use App\Core\Models\Rank;

// Get all ranks ordered
$ranks = Rank::orderBy('min_level')->get();

// Rank properties
$rank->name;
$rank->min_level;
$rank->exp_multiplier;
```

---

## Core Services

### PluginManagerService

Manage plugins programmatically:

```php
use App\Core\Services\PluginManagerService;

$manager = app(PluginManagerService::class);

// Get all plugins
$plugins = $manager->getAllPlugins();

// Install/enable/disable
$manager->install('plugin-slug');
$manager->enable('plugin-slug');
$manager->disable('plugin-slug');
$manager->uninstall('plugin-slug');

// Get staging plugins
$staging = $manager->getStagingPlugins();

// Get themes
$themes = $manager->getAllThemes();
```

### HookService

Hook system for inter-plugin communication:

```php
use App\Core\Services\HookService;
// Or use the facade
use App\Facades\Hook;

// Register hook
Hook::register('hookName', function($data) {
    // Handle hook
}, 10);

// Run action hook
Hook::action('afterSomething', $data);

// Run filter hook
$modified = Hook::filter('modifyValue', $value, $context);
```

### SettingService

Cached settings management:

```php
use App\Core\Services\SettingService;

$settings = app(SettingService::class);

// Get setting with cache
$value = $settings->get('key', 'default');

// Set setting (clears cache)
$settings->set('key', 'value');

// Get multiple settings
$values = $settings->getMany(['key1', 'key2']);

// Refresh cache
$settings->refresh();
```

### TimerService

Player cooldown management:

```php
use App\Core\Services\TimerService;

$timers = app(TimerService::class);

// Set timer for user
$timers->set($user, 'crime', 60); // 60 seconds

// Check timer
$timers->has($user, 'crime');     // bool
$timers->get($user, 'crime');     // remaining seconds

// Clear timer
$timers->clear($user, 'crime');

// Get all user timers
$timers->getAll($user);
```

### NotificationService

User notifications:

```php
use App\Core\Services\NotificationService;

$notifications = app(NotificationService::class);

// Send notification
$notifications->send($user, [
    'type' => 'info',
    'title' => 'Welcome!',
    'message' => 'Welcome to the game!',
    'link' => '/dashboard',
]);

// Get unread count
$count = $notifications->unreadCount($user);

// Mark all read
$notifications->markAllRead($user);
```

### WebhookService

Webhook delivery:

```php
use App\Core\Services\WebhookService;

$webhooks = app(WebhookService::class);

// Dispatch webhook event
$webhooks->dispatch('user.registered', [
    'user_id' => $user->id,
    'username' => $user->username,
    'email' => $user->email,
]);

// Available events
$events = $webhooks->getAvailableEvents();
```

### TwoFactorAuthService

Two-factor authentication:

```php
use App\Core\Services\TwoFactorAuthService;

$twoFactor = app(TwoFactorAuthService::class);

// Generate secret
$secret = $twoFactor->generateSecret();

// Generate QR code
$qrCode = $twoFactor->getQRCode($user, $secret);

// Verify code
$valid = $twoFactor->verify($secret, $code);

// Generate recovery codes
$codes = $twoFactor->generateRecoveryCodes();
```

### CacheService

Cache utilities:

```php
use App\Core\Services\CacheService;

$cache = app(CacheService::class);

// User-specific cache
$cache->rememberForUser($user, 'key', 3600, function() {
    return expensiveOperation();
});

// Clear user cache
$cache->clearUser($user);

// Cache tags
$cache->tags(['users', 'stats'])->remember('key', 3600, fn() => data());
```

### ModerationService

Ban and warning management:

```php
use App\Core\Services\ModerationService;

$moderation = app(ModerationService::class);

// Ban user
$moderation->ban($user, [
    'reason' => 'Cheating',
    'duration' => 7, // days, null for permanent
    'banned_by' => $admin->id,
]);

// Unban
$moderation->unban($user);

// Warn user
$moderation->warn($user, [
    'reason' => 'Inappropriate behavior',
    'warned_by' => $admin->id,
]);

// Get user warnings
$warnings = $moderation->getWarnings($user);
```

---

## Core Controllers

### AuthController

Authentication endpoints:

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login user |
| POST | `/api/logout` | Logout user |
| GET | `/api/user` | Get current user |
| POST | `/api/user/change-password` | Change password |

### Admin Controllers

All admin endpoints require `auth:sanctum` and `role:admin|moderator` middleware:

```php
// Dashboard
GET  /api/admin/stats                    // Dashboard statistics

// Users
GET  /api/admin/users                    // List users
POST /api/admin/users                    // Create user
GET  /api/admin/users/{id}               // Get user
PATCH /api/admin/users/{id}              // Update user
DELETE /api/admin/users/{id}             // Delete user
POST /api/admin/users/{id}/ban           // Ban user
POST /api/admin/users/{id}/unban         // Unban user

// Roles
GET  /api/admin/roles                    // List roles
POST /api/admin/roles                    // Create role
PATCH /api/admin/roles/{id}              // Update role
DELETE /api/admin/roles/{id}             // Delete role

// Settings
GET  /api/admin/settings                 // Get all settings
POST /api/admin/settings                 // Create setting
PATCH /api/admin/settings                // Update settings

// Plugins
GET  /api/admin/plugins                  // List plugins
POST /api/admin/plugins/upload           // Upload plugin
POST /api/admin/plugins/{slug}/install   // Install plugin
PUT  /api/admin/plugins/{slug}/enable    // Enable plugin
PUT  /api/admin/plugins/{slug}/disable   // Disable plugin
DELETE /api/admin/plugins/{slug}         // Uninstall plugin
```

---

## Core Middleware

### Authentication Middleware

```php
// Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authenticated routes
});
```

### Role/Permission Middleware

```php
// Require specific role
Route::middleware('role:admin')->group(function () {
    // Admin only
});

// Multiple roles
Route::middleware('role:admin|moderator')->group(function () {
    // Admin or moderator
});

// Require permission
Route::middleware('permission:manage-users')->group(function () {
    // Must have permission
});
```

### Rate Limiting

```php
// Limit to 10 requests per minute
Route::middleware('throttle:10,1')->group(function () {
    // Rate limited routes
});
```

---

## Core Events

The Core system dispatches these events:

```php
// User events
App\Core\Events\UserRegistered::class
App\Core\Events\UserLoggedIn::class
App\Core\Events\UserLoggedOut::class
App\Core\Events\UserLeveledUp::class
App\Core\Events\UserBanned::class

// System events
App\Core\Events\SettingChanged::class
App\Core\Events\PluginInstalled::class
App\Core\Events\PluginEnabled::class
App\Core\Events\PluginDisabled::class
```

Listen to events:

```php
// In EventServiceProvider
protected $listen = [
    UserRegistered::class => [
        SendWelcomeEmail::class,
        CreateStarterInventory::class,
    ],
];
```

---

## Core Configuration

### `config/app.php`

```php
return [
    'name' => env('APP_NAME', 'LaravelCP'),
    'timezone' => 'UTC',
    'locale' => 'en',
];
```

### `config/plugins.php`

```php
return [
    'path' => app_path('Plugins'),
    'namespace' => 'App\\Plugins',
    'auto_discover' => true,
];
```

### `config/sanctum.php`

```php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
    'expiration' => null,  // Tokens don't expire
];
```

---

## Extending Core

### Adding Core Services

1. Create service in `app/Core/Services/`:

```php
<?php

namespace App\Core\Services;

class MyService
{
    public function doSomething(): void
    {
        // ...
    }
}
```

2. Register in service provider:

```php
// app/Core/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(MyService::class);
}
```

### Adding Core Models

1. Create model in `app/Core/Models/`
2. Create migration in `database/migrations/`
3. Add relationships to User model if needed

### Adding Core Controllers

1. Create controller in `app/Core/Http/Controllers/`
2. Add routes in `routes/api.php`

---

## Next Steps

- [Plugin System](Plugin-System) - How plugins extend Core
- [Services](Services) - Deep dive into services
- [Routes & Controllers](Routes-and-Controllers) - API development
