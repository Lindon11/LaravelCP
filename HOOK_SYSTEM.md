# Hook System Documentation

## Overview

The Hook System provides a WordPress-style extensibility pattern for Gangster Legends V3, mirroring the V2 architecture while using modern Laravel patterns.

## Basic Usage

### Registering a Hook

```php
use App\Facades\Hook;

// Simple hook
Hook::register('hookName', function($data) {
    // Modify and return data
    return $data;
});

// With priority (higher runs first)
Hook::register('hookName', function($data) {
    return $data;
}, 20);
```

### Running Hooks

```php
// Filter style (passes data through each callback)
$modifiedValue = Hook::run('hookName', $initialValue, true);

// Or use the filter alias
$modifiedValue = Hook::filter('hookName', $initialValue);

// Action style (collects results from all callbacks)
$results = Hook::run('hookName', $data);

// Or use the action alias
$results = Hook::action('hookName', $data);
```

## Core Hooks

### Navigation & UI

#### `customMenus`
Add navigation menu items.

```php
Hook::register('customMenus', function($user) {
    return [
        'actions' => [
            'title' => 'Actions',
            'items' => [
                ['url' => route('crimes'), 'text' => 'Crimes', 'sort' => 100],
                ['url' => route('gym'), 'text' => 'Gym', 'sort' => 200],
            ],
            'sort' => 100,
        ]
    ];
});
```

#### `alterTemplateData`
Modify data before rendering views.

```php
Hook::register('alterTemplateData', function($data) {
    if ($data['page'] === 'dashboard') {
        $data['custom_widget'] = getCustomData();
    }
    return $data;
});
```

### Module System

#### `moduleLoad`
Intercept page loading (for redirects like hospital/jail).

```php
Hook::register('moduleLoad', function($moduleName) {
    if (auth()->user()->isInHospital()) {
        return 'hospital'; // Redirect to hospital
    }
    return $moduleName;
});
```

#### `alterModuleData`
Modify module data (for membership benefits, modifiers).

```php
Hook::register('alterModuleData', function($data) {
    if ($data['module'] === 'crimes' && $data['user']->hasMembership()) {
        // Increase success rate by 25%
        $data['data']['success_rate'] *= 1.25;
    }
    return $data;
});
```

### User Actions

#### `beforeUserAction`
Run before any user action.

```php
Hook::register('beforeUserAction', function($action) {
    // Validate, log, or modify action
    return $action;
});
```

#### `afterUserAction`
Run after user actions complete.

```php
Hook::register('afterUserAction', function($action) {
    // Update achievements, statistics, send notifications
    if ($action['success']) {
        trackAchievement($action['user'], $action['module']);
    }
    return $action;
});
```

### Formatting

#### `currencyFormat`
Format money values.

```php
Hook::register('currencyFormat', function($money) {
    return '$' . number_format($money);
});

// Usage
$formatted = Hook::filter('currencyFormat', 1000000); // "$1,000,000"
```

#### `userLevelColor`
Get color for user levels.

```php
Hook::register('userLevelColor', function($level) {
    return match($level) {
        1 => '#22c55e',
        2 => '#3b82f6',
        3 => '#ef4444',
        default => '#6b7280',
    };
});
```

### Admin Panel

#### `adminWidget-stats`
Add dashboard widgets.

```php
Hook::register('adminWidget-stats', function($user) {
    return [
        'title' => 'User Stats',
        'size' => 6,
        'data' => ['users' => User::count()],
    ];
});
```

## Module Integration

### Creating a Module with Hooks

**Directory Structure:**
```
app/Modules/CrimeModule/
├── hooks.php              # Hook registrations
├── CrimeController.php    # Laravel controller
├── routes.php             # Module routes
└── views/                 # Blade templates
```

**hooks.php Example:**
```php
<?php

use App\Facades\Hook;

// Add to navigation
Hook::register('customMenus', function($user) {
    return [
        'crimes' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => route('crimes.index'),
                    'text' => 'Crimes',
                    'timer' => $user->getTimer('crime'),
                ]
            ]
        ]
    ];
});

// Track crime attempts
Hook::register('afterUserAction', function($action) {
    if ($action['module'] === 'crimes') {
        CrimeLog::create($action);
    }
    return $action;
});
```

## Using Hooks in Controllers

```php
class CrimeController extends Controller
{
    public function attempt(Request $request)
    {
        // Get crime data
        $crime = Crime::find($request->crime_id);
        
        // Apply modifiers via hook
        $crime = Hook::filter('alterModuleData', [
            'module' => 'crimes',
            'user' => auth()->user(),
            'data' => $crime->toArray(),
        ])['data'];
        
        // Before hook
        Hook::action('beforeCrimeAttempt', [
            'user_id' => auth()->id(),
            'crime_id' => $crime->id,
        ]);
        
        // Attempt crime
        $success = $this->attemptCrime($crime);
        
        // After hook
        Hook::action('afterUserAction', [
            'user' => auth()->id(),
            'module' => 'crimes',
            'id' => $crime->id,
            'success' => $success,
            'reward' => $success ? $crime->reward : 0,
        ]);
        
        return response()->json(['success' => $success]);
    }
}
```

## Using Hooks in Blade/Vue

### In Blade Templates

```php
// Format currency
{{ Hook::filter('currencyFormat', $user->money) }}

// Get user color
<span style="color: {{ Hook::filter('userLevelColor', $user->level) }}">
    {{ $user->name }}
</span>
```

### In Inertia/Vue

Pass hook results via Inertia props:

```php
// Controller
return Inertia::render('Dashboard', [
    'menus' => Hook::action('customMenus', auth()->user()),
    'widgets' => Hook::action('dashboardWidgets', auth()->user()),
]);
```

```vue
<!-- Vue Component -->
<template>
  <nav v-for="menu in menus" :key="menu.title">
    <h3>{{ menu.title }}</h3>
    <a v-for="item in menu.items" :href="item.url">
      {{ item.text }}
    </a>
  </nav>
</template>

<script setup>
const props = defineProps(['menus', 'widgets']);
</script>
```

## Priority System

Hooks with higher priority run first:

```php
Hook::register('hookName', $callback1, 10);  // Runs third
Hook::register('hookName', $callback2, 50);  // Runs first
Hook::register('hookName', $callback3, 20);  // Runs second
```

## Debugging

```php
// Check if hook exists
if (Hook::has('hookName')) {
    // Hook is registered
}

// Count callbacks
$count = Hook::count('hookName');

// Get all registered hooks
$hooks = Hook::getHookNames();

// Get detailed debug info
$info = Hook::getDebugInfo();
// Returns: ['hookName' => ['callback_count' => 3, 'execution_count' => 5, ...]]
```

## Best Practices

1. **Use Descriptive Hook Names**
   ```php
   // Good
   Hook::register('beforeCrimeAttempt', ...);
   
   // Bad
   Hook::register('crime', ...);
   ```

2. **Always Return Data in Filters**
   ```php
   Hook::register('alterData', function($data) {
       $data['modified'] = true;
       return $data; // Don't forget!
   });
   ```

3. **Use Priority for Order Control**
   ```php
   // Run validation first
   Hook::register('beforeAction', $validateAction, 100);
   
   // Then apply modifiers
   Hook::register('beforeAction', $applyModifiers, 50);
   ```

4. **Keep Hooks Focused**
   - One hook should do one thing
   - Create specific hooks rather than catch-all hooks

5. **Document Your Hooks**
   - Comment what data is expected
   - Document return values
   - List available hooks in your module README

## Migration from V2

### V2 Pattern
```php
// V2
new hook("hookName", function ($data) {
    return $data;
});

$hook = new Hook("hookName");
$result = $hook->run($data, true);
```

### V3 Pattern
```php
// V3
use App\Facades\Hook;

Hook::register('hookName', function ($data) {
    return $data;
});

$result = Hook::filter('hookName', $data);
```

## Common Hook Patterns

### 1. Membership Benefits
```php
Hook::register('alterModuleData', function($data) {
    if ($data['user']->hasMembership() && $data['module'] === 'crimes') {
        $data['data']['timer'] *= 0.75; // 25% faster
    }
    return $data;
});
```

### 2. Achievement System
```php
Hook::register('afterUserAction', function($action) {
    if ($action['success'] && $action['module'] === 'crimes') {
        Achievement::check($action['user'], 'crime_master', [
            'total_crimes' => CrimeLog::where('user_id', $action['user'])->count()
        ]);
    }
    return $action;
});
```

### 3. Statistics Tracking
```php
Hook::register('afterUserAction', function($action) {
    UserStat::increment($action['user'], $action['module'] . '_attempts');
    if ($action['success']) {
        UserStat::increment($action['user'], $action['module'] . '_successes');
    }
    return $action;
});
```

### 4. Notification System
```php
Hook::register('afterUserAction', function($action) {
    if ($action['module'] === 'attack' && $action['target']) {
        Notification::create([
            'user_id' => $action['target'],
            'type' => 'attacked',
            'data' => ['attacker' => $action['user']],
        ]);
    }
    return $action;
});
```

## API Reference

### HookService Methods

- `register(string $hookName, Closure $callback, int $priority = 10): void`
- `run(string $hookName, mixed $data = null, bool $returnSingle = false): mixed`
- `filter(string $hookName, mixed $value): mixed` - Alias for run with returnSingle
- `action(string $hookName, mixed $data = null): array` - Alias for run
- `has(string $hookName): bool`
- `count(string $hookName): int`
- `clear(string $hookName): void`
- `clearAll(): void`
- `getHookNames(): array`
- `getDebugInfo(): array`
