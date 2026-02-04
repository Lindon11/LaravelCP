# Plugin System

LaravelCP uses a powerful plugin architecture to organize game features. Each plugin is a self-contained module that can be installed, enabled, disabled, or removed independently.

---

## Overview

Plugins are stored in `app/Plugins/` and follow a consistent structure. The system automatically discovers and loads enabled plugins on application boot.

### Built-in Plugins (28 total)

| Plugin | Description |
|--------|-------------|
| **Achievements** | Player achievement tracking |
| **Alliances** | Multi-gang alliances |
| **Announcements** | Game announcements |
| **Bank** | Banking system with interest |
| **Bounty** | Player bounty hunting |
| **Bullets** | Ammunition system |
| **Casino** | Gambling games & lottery |
| **Chat** | Real-time chat system |
| **Combat** | PvE/NPC combat system |
| **Crimes** | Solo crime actions |
| **DailyRewards** | Daily login rewards |
| **Detective** | Investigation system |
| **Drugs** | Drug dealing simulation |
| **Education** | Skill courses |
| **Employment** | Job system |
| **Events** | Timed game events |
| **Forum** | Community forum |
| **Gang** | Gang/crew management |
| **Gym** | Stat training |
| **Hospital** | Health recovery |
| **Inventory** | Item management |
| **Jail** | Jail system |
| **Leaderboards** | Player rankings |
| **Market** | Player trading |
| **Messaging** | Private messages |
| **Missions** | Mission/quest system |
| **OrganizedCrime** | Group crimes |
| **Properties** | Real estate |
| **Quests** | Quest chains |
| **Racing** | Car racing |
| **Stocks** | Stock market |
| **Theft** | Grand theft auto |
| **Tickets** | Support tickets |
| **Tournament** | Competitions |
| **Travel** | Location travel |
| **Wiki** | FAQ & wiki pages |

---

## Plugin Structure

Each plugin follows this directory structure:

```
app/Plugins/YourPlugin/
├── plugin.json                 # Required: Plugin metadata
├── YourPluginModule.php        # Main module class
├── hooks.php                   # Hook registrations
├── Controllers/                # HTTP controllers
│   ├── YourPluginController.php
│   └── YourPluginManagementController.php
├── Models/                     # Eloquent models
│   └── YourModel.php
├── Services/                   # Business logic
│   └── YourPluginService.php
├── routes/                     # Route definitions
│   ├── web.php                 # Web routes
│   ├── api.php                 # API routes
│   └── admin.php               # Admin routes
├── resources/                  # Views and assets
│   └── views/
├── database/                   # Database files
│   ├── migrations/
│   └── seeders/
└── lang/                       # Translations
    └── en/
```

---

## Plugin Configuration (`plugin.json`)

Every plugin requires a `plugin.json` file:

```json
{
    "name": "Crimes",
    "slug": "crimes",
    "version": "3.0.0",
    "description": "Commit various crimes to earn cash and experience",
    "author": "OpenPBBG",
    "enabled": true,
    "requires": {
        "laravel": "^11.0",
        "modules": []
    },
    "settings": {
        "icon": "🔫",
        "color": "red",
        "route": "crimes.index",
        "menu": {
            "enabled": true,
            "order": 1,
            "section": "actions",
            "parent": null
        },
        "permissions": {
            "view": "level:1",
            "use": "level:1"
        }
    },
    "hooks": {
        "OnCrimeCommit": true,
        "alterModuleData": true,
        "moduleLoad": true
    },
    "routes": {
        "web": true,
        "api": true,
        "admin": false
    }
}
```

### Configuration Fields

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Display name |
| `slug` | string | Unique identifier (lowercase, no spaces) |
| `version` | string | Semantic version |
| `description` | string | Plugin description |
| `author` | string | Plugin author |
| `enabled` | boolean | Whether plugin is active |
| `requires.laravel` | string | Laravel version constraint |
| `requires.modules` | array | Dependent plugins |
| `settings.icon` | string | Menu icon (emoji or icon class) |
| `settings.color` | string | Theme color |
| `settings.menu` | object | Menu configuration |
| `hooks` | object | Registered hooks |
| `routes.web` | boolean | Load web routes |
| `routes.api` | boolean | Load API routes |
| `routes.admin` | boolean | Load admin routes |

---

## Base Plugin Class

All plugins should extend the base `Plugin` class:

```php
<?php

namespace App\Plugins\YourPlugin;

use App\Plugins\Plugin;
use App\Core\Models\User;

class YourPluginModule extends Plugin
{
    protected string $name = 'YourPlugin';
    
    protected array $config = [];
    
    /**
     * Initialize the plugin
     */
    public function construct(): void
    {
        $this->config = [
            'some_setting' => 100,
            'another_setting' => true,
        ];
    }
    
    /**
     * Check if user can access this plugin
     */
    public function canAccess(User $user): bool
    {
        return $user->level >= 1;
    }
    
    /**
     * Handle plugin actions
     */
    public function handleAction(string $action, array $data): mixed
    {
        $methodName = 'action' . ucfirst($action);
        
        if (method_exists($this, $methodName)) {
            return $this->$methodName($data);
        }
        
        return ['error' => 'Action not found'];
    }
}
```

### Available Helper Methods

The base `Plugin` class provides:

```php
// Add HTML output
$this->addHtml('<div>Content</div>');

// Display alerts
$this->success('Operation successful!');
$this->error('Something went wrong');
$this->info('Information message');
$this->warning('Warning message');

// Format values
$this->money(1000);  // Returns formatted currency
$this->date($timestamp);  // Returns formatted date

// Build views
$html = $this->buildElement('view-name', ['data' => $value]);

// Apply hooks
$data = $this->applyModuleHook('hookName', $data);

// Track user actions
$this->trackAction('action_type', $data);

// Validate action data
$validated = $this->validateMethod('actionName', $requestData);
```

---

## Plugin Lifecycle

### Installation

When a plugin is installed:

1. Plugin ZIP is uploaded to `storage/plugins/installing/`
2. Admin reviews and clicks "Install"
3. Files are moved to `app/Plugins/`
4. Migrations are run
5. Plugin is registered in `installed_plugins` table
6. Routes and hooks are loaded

### Enabling/Disabling

```php
// Enable a plugin
$pluginManager->enable('your-plugin');

// Disable a plugin
$pluginManager->disable('your-plugin');
// Files moved to storage/plugins/disabled/
```

### Uninstalling

1. Plugin is disabled
2. Optional: Migrations are rolled back
3. Files are deleted
4. Database record is removed

---

## Plugin Configuration (`config/plugins.php`)

System-wide plugin settings:

```php
<?php

return [
    // Plugin directory path
    'path' => app_path('Plugins'),
    
    // Plugin namespace
    'namespace' => 'App\\Plugins',
    
    // Auto-discover plugins on boot
    'auto_discover' => true,
    
    // Expected directory structure
    'structure' => [
        'routes' => ['web.php', 'api.php', 'admin.php'],
        'controllers' => 'Controllers',
        'models' => 'Models',
        'views' => 'views',
        'migrations' => 'database/migrations',
        'seeders' => 'database/seeders',
        'hooks' => 'hooks.php',
        'config' => 'config.php',
        'assets' => 'assets',
        'lang' => 'lang',
    ],
    
    // Middleware applied to plugin routes
    'middleware' => [
        'api' => ['api', 'auth:sanctum'],
        'web' => ['web'],
        'admin' => ['api', 'auth:sanctum', 'role:admin'],
    ],
];
```

---

## Plugin Manager Service

Interact with plugins programmatically:

```php
use App\Core\Services\PluginManagerService;

$manager = app(PluginManagerService::class);

// Get all plugins
$plugins = $manager->getAllPlugins();

// Get staging plugins (awaiting installation)
$staging = $manager->getStagingPlugins();

// Get disabled plugins
$disabled = $manager->getDisabledPlugins();

// Install a plugin
$manager->install('plugin-slug');

// Enable/disable
$manager->enable('plugin-slug');
$manager->disable('plugin-slug');

// Uninstall
$manager->uninstall('plugin-slug');
```

---

## Next Steps

- [Creating Plugins](Creating-Plugins) - Step-by-step guide
- [Hook System](Hook-System) - Inter-plugin communication
- [Routes & Controllers](Routes-and-Controllers) - Plugin routing
