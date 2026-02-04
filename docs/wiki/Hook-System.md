# Hook System

LaravelCP includes a powerful hook system that allows plugins to communicate and extend each other's functionality. It's similar to WordPress's action/filter system.

---

## Overview

The Hook System provides:

- **Actions**: Execute code at specific points (fire-and-forget)
- **Filters**: Modify data as it passes through hooks
- **Priority-based execution**: Control execution order
- **Inter-plugin communication**: Plugins can react to other plugins' events

---

## Using Hooks

### Registering a Hook

Use the `Hook` facade to register callbacks:

```php
use App\Facades\Hook;

// Register an action hook
Hook::register('afterUserLogin', function ($data) {
    // Do something when user logs in
    logger('User logged in: ' . $data['user']->name);
}, 10);  // priority (higher runs first)

// Register with higher priority
Hook::register('afterUserLogin', function ($data) {
    // This runs FIRST (priority 20 > 10)
    $data['user']->updateLoginStreak();
}, 20);
```

### Running Actions

Execute all registered callbacks:

```php
// Run action hook (doesn't return values)
Hook::action('afterUserLogin', [
    'user' => $user,
    'ip' => request()->ip(),
    'timestamp' => now(),
]);
```

### Using Filters

Filter hooks pass data through each callback and return the modified result:

```php
// Register filter
Hook::register('calculateReward', function ($reward) {
    // VIP players get 10% bonus
    if (auth()->user()->hasVipMembership()) {
        return $reward * 1.10;
    }
    return $reward;
}, 10);

// Apply filter
$reward = 1000;
$finalReward = Hook::filter('calculateReward', $reward);
```

### Filtering with Context

Pass additional context to filters:

```php
// Register filter with context
Hook::register('calculateCrimeSuccessRate', function ($rate, $context) {
    $crime = $context['crime'];
    $user = $context['user'];
    
    // Add skill bonus
    if ($user->hasSkill('lockpicking')) {
        $rate += 0.05;
    }
    
    return min(0.95, $rate);  // Cap at 95%
}, 10);

// Apply filter with context
$successRate = Hook::filter('calculateCrimeSuccessRate', $baseRate, [
    'crime' => $crime,
    'user' => $user,
]);
```

---

## Common Hooks

### System Hooks

| Hook Name | Type | Description |
|-----------|------|-------------|
| `afterUserLogin` | action | User successfully logged in |
| `afterUserLogout` | action | User logged out |
| `afterUserRegister` | action | New user registered |
| `beforeUserBan` | action | User about to be banned |
| `afterUserLevelUp` | action | User gained a level |
| `dailyReset` | action | Daily reset cron runs |

### Economy Hooks

| Hook Name | Type | Description |
|-----------|------|-------------|
| `currencyFormat` | filter | Format currency display |
| `alterCashReward` | filter | Modify cash rewards |
| `afterCashTransaction` | action | Cash was transferred |
| `beforePurchase` | filter | Before item purchase |

### Combat Hooks

| Hook Name | Type | Description |
|-----------|------|-------------|
| `calculateDamage` | filter | Modify damage dealt |
| `calculateDefense` | filter | Modify defense value |
| `afterCombatVictory` | action | Player won combat |
| `afterCombatDefeat` | action | Player lost combat |

### Menu Hooks

| Hook Name | Type | Description |
|-----------|------|-------------|
| `customMenus` | filter | Add items to navigation |
| `alterMenuBadge` | filter | Modify menu badges |

---

## Creating Hooks in Your Plugin

### In Your Plugin's `hooks.php`

```php
<?php
// app/Plugins/YourPlugin/hooks.php

use App\Facades\Hook;

// Add menu items
Hook::register('customMenus', function ($user) {
    if (!$user) return [];
    
    return [
        'your_plugin' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('your-plugin.index'),
                    'text' => 'Your Plugin',
                    'icon' => '🎮',
                    'timer' => $user->getTimer('your_plugin'),
                    'badge' => $user->yourPluginNotifications()->count(),
                    'sort' => 100,
                ],
            ],
        ],
    ];
}, 10);

// Listen to other plugin events
Hook::register('afterCrimeAttempt', function ($data) {
    // React when user commits a crime
    if ($data['success']) {
        // Maybe unlock something in your plugin
    }
}, 10);

// Expose your own hooks
Hook::register('afterYourPluginAction', function ($data) {
    // Other plugins can listen to this
}, 10);
```

### In Your Module Class

```php
<?php

namespace App\Plugins\YourPlugin;

use App\Plugins\Plugin;
use App\Facades\Hook;

class YourPluginModule extends Plugin
{
    public function construct(): void
    {
        // Your plugin can also register hooks here
    }
    
    public function doSomething($user): array
    {
        // Apply filters before action
        $config = Hook::filter('yourPluginConfig', $this->config, [
            'user' => $user,
        ]);
        
        // Do your action...
        $result = $this->performAction($user, $config);
        
        // Fire action hook after
        Hook::action('afterYourPluginAction', [
            'user' => $user,
            'result' => $result,
        ]);
        
        return $result;
    }
}
```

### In Your Service

```php
<?php

namespace App\Plugins\YourPlugin\Services;

use App\Facades\Hook;

class YourPluginService
{
    public function calculateReward($baseReward, $user)
    {
        // Apply filters from other plugins
        $reward = Hook::filter('alterYourPluginReward', $baseReward, [
            'user' => $user,
        ]);
        
        // Apply global reward filter
        $reward = Hook::filter('alterCashReward', $reward, [
            'source' => 'your_plugin',
            'user' => $user,
        ]);
        
        return $reward;
    }
}
```

---

## Hook Service API

### Full API Reference

```php
use App\Facades\Hook;

// Register a callback
Hook::register(string $hookName, Closure $callback, int $priority = 10): void

// Run all callbacks (action style - no return)
Hook::action(string $hookName, mixed $data = null): void

// Run callbacks and return modified data (filter style)
Hook::filter(string $hookName, mixed $data, array $context = []): mixed

// Check if hook has callbacks
Hook::has(string $hookName): bool

// Count callbacks for a hook
Hook::count(string $hookName): int

// Clear all callbacks for a hook
Hook::clear(string $hookName): void

// Get all registered hooks
Hook::all(): array

// Get execution statistics
Hook::getStats(): array
```

---

## Using the Base Plugin's Hook Helpers

The base `Plugin` class provides convenient methods:

```php
<?php

namespace App\Plugins\YourPlugin;

use App\Plugins\Plugin;

class YourPluginModule extends Plugin
{
    public function construct(): void
    {
        // ...
    }
    
    public function doAction($user, $data)
    {
        // Apply module-specific hook
        $data = $this->applyModuleHook('alterActionData', $data);
        
        // Perform action...
        $result = $this->performAction($data);
        
        // Track for analytics
        $this->trackAction('action_performed', [
            'result' => $result,
            'data' => $data,
        ]);
        
        return $result;
    }
}
```

The `applyModuleHook` method automatically includes:
- Module name
- Current user
- Provided data

---

## Example: Cross-Plugin Integration

### Plugin A: Crimes (fires hook)

```php
// In CrimesModule.php
public function attemptCrime($user, $crime): array
{
    // ... crime logic ...
    
    // Fire hook after successful crime
    if ($success) {
        Hook::action('afterCrimeCommit', [
            'user' => $user,
            'crime' => $crime,
            'reward' => $cashEarned,
            'exp' => $expEarned,
        ]);
    }
    
    return $result;
}
```

### Plugin B: Achievements (listens to hook)

```php
// In achievements/hooks.php
Hook::register('afterCrimeCommit', function ($data) {
    $user = $data['user'];
    
    // Check achievement progress
    $crimesCommitted = $user->crimeAttempts()
        ->where('success', true)
        ->count();
    
    // Award achievement
    if ($crimesCommitted >= 100) {
        app(AchievementService::class)->award($user, 'master_criminal');
    }
}, 10);
```

### Plugin C: Missions (listens to hook)

```php
// In missions/hooks.php
Hook::register('afterCrimeCommit', function ($data) {
    $user = $data['user'];
    
    // Update mission progress
    app(MissionService::class)->updateProgress(
        $user,
        'commit_crime',
        1
    );
}, 10);
```

---

## Best Practices

### 1. Use Descriptive Hook Names

```php
// Good
Hook::action('afterUserPurchasedItem', $data);
Hook::filter('calculateCombatDamage', $damage);

// Bad
Hook::action('hook1', $data);
Hook::filter('modify', $data);
```

### 2. Document Your Hooks

In your plugin's README or documentation:

```markdown
## Available Hooks

### Actions

- `afterYourPluginAction` - Fired after main action completes
  - `user`: The user who performed the action
  - `result`: The action result
  - `timestamp`: When it occurred

### Filters

- `alterYourPluginReward` - Modify reward before giving to user
  - Input: `$reward` (int)
  - Context: `['user' => User, 'action' => string]`
  - Return: Modified reward (int)
```

### 3. Use Priorities Wisely

```php
// Priority 30 - Run early (validation)
Hook::register('beforePurchase', function ($data) {
    // Validate purchase
}, 30);

// Priority 20 - Run in middle (modifications)
Hook::register('beforePurchase', function ($data) {
    // Apply discounts
}, 20);

// Priority 10 - Run late (logging)
Hook::register('beforePurchase', function ($data) {
    // Log attempt
}, 10);
```

### 4. Handle Missing Hooks Gracefully

```php
// Filter returns original value if no hooks registered
$reward = Hook::filter('nonExistentHook', $reward);
// $reward is unchanged

// Check before complex operations
if (Hook::has('expensiveHook')) {
    $data = Hook::filter('expensiveHook', $data);
}
```

---

## Next Steps

- [Creating Plugins](Creating-Plugins) - Build plugins that use hooks
- [Services](Services) - Business logic with hooks
- [Core System](Core-System) - Core hooks available
