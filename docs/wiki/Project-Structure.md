# Project Structure

Understanding the LaravelCP directory structure and architecture.

---

## Directory Overview

```text
LaravelCP/
├── app/                        # Application code
│   ├── Actions/                # Single-purpose action classes
│   ├── Console/                # Artisan commands
│   │   └── Commands/           # Custom CLI commands
│   ├── Core/                   # ⭐ Essential system components
│   │   ├── Events/             # System events
│   │   ├── Facades/            # Laravel facades
│   │   ├── Helpers/            # Helper functions
│   │   ├── Http/               # Controllers & Middleware
│   │   │   ├── Controllers/    # API controllers
│   │   │   │   ├── Admin/      # Admin panel controllers
│   │   │   │   └── Auth/       # Authentication controllers
│   │   │   └── Middleware/     # HTTP middleware
│   │   ├── Models/             # Core Eloquent models
│   │   ├── Observers/          # Model observers
│   │   ├── Providers/          # Service providers
│   │   └── Services/           # Business logic services
│   ├── Facades/                # Application facades
│   ├── Http/                   # Legacy HTTP layer
│   ├── Mail/                   # Mailable classes
│   ├── Modules/                # Legacy modules (deprecated)
│   ├── Plugins/                # ⭐ Game feature plugins
│   │   ├── Plugin.php          # Base plugin class
│   │   ├── Achievements/       
│   │   ├── Bank/               
│   │   ├── Combat/             
│   │   ├── Crimes/             # Example plugin
│   │   └── ...                 # 28 plugins total
│   └── Policies/               # Authorization policies
├── bootstrap/                  # Application bootstrapping
│   ├── app.php                 # Application instance
│   └── providers.php           # Service provider registration
├── config/                     # Configuration files
│   ├── app.php                 # Application config
│   ├── auth.php                # Authentication config
│   ├── database.php            # Database config
│   ├── plugins.php             # ⭐ Plugin system config
│   └── ...                     
├── database/                   # Database files
│   ├── factories/              # Model factories
│   ├── migrations/             # Database migrations
│   └── seeders/                # Database seeders
├── public/                     # Publicly accessible files
│   ├── index.php               # Application entry point
│   ├── admin/                  # Built admin panel assets
│   └── install/                # Web installer assets
├── resources/                  # Frontend resources
│   ├── admin/                  # ⭐ Vue.js admin panel source
│   │   ├── src/
│   │   │   ├── components/     # Vue components
│   │   │   ├── views/          # Page views
│   │   │   ├── router/         # Vue Router
│   │   │   └── stores/         # Pinia stores
│   │   └── package.json
│   └── views/                  # Blade templates
├── routes/                     # Route definitions
│   ├── api.php                 # ⭐ API routes
│   ├── web.php                 # Web routes
│   └── console.php             # Console routes
├── storage/                    # Application storage
│   ├── app/                    # Application files
│   ├── framework/              # Framework cache
│   ├── logs/                   # Log files
│   └── plugins/                # Plugin storage
│       ├── disabled/           # Disabled plugins
│       └── installing/         # Staging plugins
├── tests/                      # Test files
│   ├── Feature/                # Feature tests
│   └── Unit/                   # Unit tests
├── themes/                     # Frontend themes
└── vendor/                     # Composer dependencies
```

---

## Core Components (`app/Core/`)

The Core directory contains essential system functionality that is always loaded.

### Controllers (`app/Core/Http/Controllers/`)

```text
Controllers/
├── Admin/                      # Admin panel API
│   ├── ActivityLogController.php
│   ├── ApiKeyController.php
│   ├── CacheController.php
│   ├── DashboardStatsController.php
│   ├── EmailSettingsController.php
│   ├── ErrorLogController.php
│   ├── IpBanController.php
│   ├── LocationController.php
│   ├── MembershipController.php
│   ├── RankController.php
│   ├── RolePermissionController.php
│   ├── SettingsController.php
│   ├── StaffChatController.php
│   ├── UserManagementController.php
│   ├── UserTimerController.php
│   ├── UserToolsController.php
│   └── WebhookController.php
├── Auth/                       # Authentication
│   ├── OAuthController.php
│   ├── PasswordResetController.php
│   └── TwoFactorAuthController.php
├── AuthController.php          # Main auth controller
├── EmojiController.php
├── FrontendErrorController.php
├── PluginController.php
└── WebSocketController.php
```

### Models (`app/Core/Models/`)

```text
Models/
├── User.php                    # User model
├── Setting.php                 # Game settings
├── Location.php                # Game locations
├── Rank.php                    # Player ranks
├── Membership.php              # VIP memberships
├── Item.php                    # Base item model
├── ErrorLog.php                # Error logging
├── ApiKey.php                  # API key management
├── Webhook.php                 # Webhook configuration
└── ...
```

### Services (`app/Core/Services/`)

Business logic services:

```text
Services/
├── PluginManagerService.php    # Plugin installation/management
├── HookService.php             # Hook system (events)
├── SettingService.php          # Settings management
├── TimerService.php            # Player cooldown timers
├── NotificationService.php     # User notifications
├── WebhookService.php          # Webhook delivery
├── TwoFactorAuthService.php    # 2FA implementation
├── OAuthService.php            # OAuth integration
├── CacheService.php            # Cache helpers
├── ModerationService.php       # Ban/warning system
└── ...
```

---

## Plugin Structure (`app/Plugins/`)

Each plugin follows a consistent structure:

```text
Plugins/
└── Crimes/                     # Example plugin
    ├── plugin.json             # ⭐ Plugin metadata
    ├── CrimesModule.php        # Main plugin class
    ├── hooks.php               # Hook registrations
    ├── Controllers/            # HTTP controllers
    │   ├── CrimeController.php
    │   └── CrimeManagementController.php
    ├── Models/                 # Eloquent models
    │   └── Crime.php
    ├── Services/               # Business logic
    │   └── CrimeService.php
    ├── routes/                 # Plugin routes
    │   └── web.php
    ├── resources/              # Views & assets
    │   └── views/
    └── database/               # Migrations & seeders
        ├── migrations/
        └── seeders/
```

---

## Configuration Files (`config/`)

Key configuration files:

| File | Purpose |
| ------ | --------- |
| `app.php` | Application name, timezone, locale |
| `auth.php` | Authentication guards & providers |
| `database.php` | Database connections |
| `plugins.php` | Plugin system configuration |
| `sanctum.php` | API authentication |
| `permission.php` | Roles & permissions (Spatie) |
| `cors.php` | CORS settings |
| `mail.php` | Email configuration |
| `cache.php` | Cache drivers |

---

## Routes (`routes/`)

### API Routes (`routes/api.php`)

The main API file is organized by functionality:

```php
// Public routes (rate limited)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    // ...
});

// Protected routes (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    
    // Admin routes
    Route::prefix('admin')->middleware('role:admin|moderator')->group(function () {
        // Admin API endpoints
    });
});
```

---

## Admin Panel (`resources/admin/`)

Vue.js 3 single-page application:

```text
resources/admin/
├── src/
│   ├── main.js                 # App entry point
│   ├── App.vue                 # Root component
│   ├── components/             # Reusable components
│   ├── views/                  # Page views
│   │   ├── DashboardHome.vue
│   │   ├── ErrorLogsView.vue
│   │   ├── UsersView.vue
│   │   └── ...
│   ├── router/                 # Vue Router config
│   │   └── index.js
│   ├── stores/                 # Pinia state stores
│   │   └── auth.js
│   └── plugins/                # Vue plugins
│       └── errorLogger.js
├── package.json
├── vite.config.js
└── index.html
```

---

## Storage (`storage/`)

```text
storage/
├── app/                        # Application uploads
│   └── backups/                # Database backups
├── framework/
│   ├── cache/                  # File cache
│   ├── sessions/               # File sessions
│   └── views/                  # Compiled views
├── logs/
│   └── laravel.log             # Application logs
└── plugins/
    ├── disabled/               # Disabled plugin storage
    └── installing/             # Plugin staging area
```

---

## Next Steps

- [Core System](Core-System) - Deep dive into core components
- [Plugin System](Plugin-System) - Understanding the plugin architecture
- [Creating Plugins](Creating-Plugins) - Build your own plugin
