# Services

Guide to the service layer architecture in LaravelCP.

---

## Overview

Services contain business logic that can be reused across controllers and plugins. LaravelCP uses two main types:

1. **Core Services** - Located in `app/Core/Services/`
2. **Plugin Services** - Located in `app/Plugins/{Plugin}/Services/`

---

## Core Services

### PluginManagerService

Manages plugin lifecycle (enable, disable, install, uninstall).

```php
use App\Core\Services\PluginManagerService;

$manager = app(PluginManagerService::class);

// Get all plugins
$plugins = $manager->getAllPlugins();

// Enable a plugin
$manager->enablePlugin('your-plugin');

// Disable a plugin
$manager->disablePlugin('your-plugin');

// Check if enabled
if ($manager->isEnabled('your-plugin')) {
    // Plugin is active
}
```

### HookService

Handles action and filter hooks for inter-plugin communication.

```php
use App\Core\Services\HookService;

$hooks = app(HookService::class);

// Register action
$hooks->addAction('user.login', function ($user) {
    // Handle login event
}, priority: 10);

// Execute action
$hooks->doAction('user.login', $user);

// Register filter
$hooks->addFilter('user.cash_earned', function ($amount, $user) {
    return $amount * 1.5; // 50% bonus
}, priority: 10);

// Apply filter
$earnedCash = $hooks->applyFilters('user.cash_earned', 100, $user);
```

### SettingService

Manages game settings stored in the database.

```php
use App\Core\Services\SettingService;

$settings = app(SettingService::class);

// Get setting
$value = $settings->get('setting_key', 'default');

// Set setting
$settings->set('setting_key', 'value');

// Get multiple
$all = $settings->all();

// Get by group
$gameSettings = $settings->getGroup('game');
```

### TimerService

Handles cooldowns and timers for actions.

```php
use App\Core\Services\TimerService;

$timers = app(TimerService::class);

// Check if action is on cooldown
if ($timers->isOnCooldown($user, 'crime_attempt')) {
    $remaining = $timers->getRemainingCooldown($user, 'crime_attempt');
    return "Wait {$remaining} seconds";
}

// Set cooldown
$timers->setCooldown($user, 'crime_attempt', 60); // 60 seconds

// Clear cooldown
$timers->clearCooldown($user, 'crime_attempt');
```

### NotificationService

Send notifications to users.

```php
use App\Core\Services\NotificationService;

$notifications = app(NotificationService::class);

// Send notification
$notifications->send($user, [
    'title' => 'Crime Complete',
    'message' => 'You earned $500',
    'type' => 'success',
    'icon' => 'check',
]);

// Mark as read
$notifications->markAsRead($notificationId);

// Get unread count
$count = $notifications->getUnreadCount($user);
```

### ErrorLogService

Log errors for debugging.

```php
use App\Core\Services\ErrorLogService;

$errorLog = app(ErrorLogService::class);

// Log error
$errorLog->log(
    exception: $e,
    context: ['user_id' => $user->id],
    severity: 'error'
);

// Log warning
$errorLog->warning('Something went wrong', ['details' => $data]);
```

---

## Creating Plugin Services

### Basic Service Structure

```php
<?php

namespace App\Plugins\YourPlugin\Services;

use App\Core\Models\User;
use App\Core\Services\HookService;
use App\Core\Services\NotificationService;
use App\Plugins\YourPlugin\Models\YourModel;
use Illuminate\Support\Facades\DB;

class YourService
{
    public function __construct(
        protected HookService $hooks,
        protected NotificationService $notifications
    ) {}
    
    /**
     * Perform an action
     */
    public function performAction(User $user, array $data): array
    {
        // Validation
        if (!$this->canPerform($user)) {
            return [
                'success' => false,
                'message' => 'Cannot perform action at this time.',
            ];
        }
        
        // Execute in transaction
        DB::transaction(function () use ($user, $data) {
            // Business logic
            $result = YourModel::create([
                'user_id' => $user->id,
                'data' => $data['value'],
            ]);
            
            // Update user
            $user->increment('stat', 10);
            
            // Fire hook
            $this->hooks->doAction('your_plugin.action_complete', $user, $result);
            
            // Notify user
            $this->notifications->send($user, [
                'title' => 'Action Complete',
                'message' => 'You have completed the action.',
                'type' => 'success',
            ]);
        });
        
        return [
            'success' => true,
            'message' => 'Action completed successfully!',
        ];
    }
    
    /**
     * Check if user can perform action
     */
    public function canPerform(User $user): bool
    {
        // Check user status
        if ($user->isInJail() || $user->isInHospital()) {
            return false;
        }
        
        // Check requirements
        if ($user->level < 5) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get statistics
     */
    public function getStats(User $user): array
    {
        return [
            'total_actions' => YourModel::where('user_id', $user->id)->count(),
            'successful' => YourModel::where('user_id', $user->id)
                ->where('success', true)
                ->count(),
            'last_action' => YourModel::where('user_id', $user->id)
                ->latest()
                ->first(),
        ];
    }
}
```

### Service with Caching

```php
<?php

namespace App\Plugins\YourPlugin\Services;

use Illuminate\Support\Facades\Cache;

class CachedService
{
    protected int $cacheTTL = 300; // 5 minutes
    
    /**
     * Get leaderboard with caching
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $cacheKey = "your_plugin.leaderboard.{$limit}";
        
        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($limit) {
            return YourModel::with('user')
                ->orderByDesc('score')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }
    
    /**
     * Clear leaderboard cache
     */
    public function clearLeaderboardCache(): void
    {
        Cache::forget('your_plugin.leaderboard.10');
        Cache::forget('your_plugin.leaderboard.25');
        Cache::forget('your_plugin.leaderboard.100');
    }
    
    /**
     * Update and invalidate cache
     */
    public function updateScore(User $user, int $score): void
    {
        YourModel::updateOrCreate(
            ['user_id' => $user->id],
            ['score' => DB::raw("score + {$score}")]
        );
        
        // Invalidate cache
        $this->clearLeaderboardCache();
    }
}
```

### Service with Events

```php
<?php

namespace App\Plugins\YourPlugin\Services;

use App\Plugins\YourPlugin\Events\ActionCompleted;

class EventDrivenService
{
    /**
     * Perform action and dispatch event
     */
    public function execute(User $user, array $data): void
    {
        $result = $this->processAction($user, $data);
        
        // Dispatch event for listeners
        event(new ActionCompleted($user, $result));
    }
}
```

---

## Registering Services

### Service Provider

```php
<?php

namespace App\Plugins\YourPlugin\Providers;

use App\Plugins\YourPlugin\Services\YourService;
use Illuminate\Support\ServiceProvider;

class YourPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind as singleton
        $this->app->singleton(YourService::class, function ($app) {
            return new YourService(
                $app->make(HookService::class),
                $app->make(NotificationService::class)
            );
        });
    }
}
```

### Using in Controllers

```php
<?php

namespace App\Plugins\YourPlugin\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\YourPlugin\Services\YourService;
use Illuminate\Http\Request;

class YourController extends Controller
{
    public function __construct(
        protected YourService $service
    ) {}
    
    public function action(Request $request)
    {
        $request->validate([
            'value' => 'required|integer|min:1',
        ]);
        
        $result = $this->service->performAction(
            $request->user(),
            $request->only('value')
        );
        
        return response()->json($result);
    }
}
```

---

## Service Design Patterns

### Repository Pattern

```php
<?php

namespace App\Plugins\YourPlugin\Repositories;

use App\Plugins\YourPlugin\Models\YourModel;
use Illuminate\Database\Eloquent\Collection;

class YourRepository
{
    public function find(int $id): ?YourModel
    {
        return YourModel::find($id);
    }
    
    public function findByUser(int $userId): Collection
    {
        return YourModel::where('user_id', $userId)->get();
    }
    
    public function create(array $data): YourModel
    {
        return YourModel::create($data);
    }
    
    public function update(int $id, array $data): bool
    {
        return YourModel::where('id', $id)->update($data);
    }
    
    public function delete(int $id): bool
    {
        return YourModel::destroy($id);
    }
}
```

### Strategy Pattern

```php
<?php

namespace App\Plugins\YourPlugin\Services\Strategies;

interface RewardStrategy
{
    public function calculate(User $user, array $context): int;
}

class BasicReward implements RewardStrategy
{
    public function calculate(User $user, array $context): int
    {
        return $context['base_reward'];
    }
}

class VIPReward implements RewardStrategy
{
    public function calculate(User $user, array $context): int
    {
        return (int) ($context['base_reward'] * 1.5);
    }
}

// Service usage
class RewardService
{
    public function getReward(User $user, array $context): int
    {
        $strategy = $user->isVIP() 
            ? new VIPReward() 
            : new BasicReward();
            
        return $strategy->calculate($user, $context);
    }
}
```

---

## Testing Services

```php
<?php

namespace Tests\Unit\Plugins\YourPlugin;

use App\Core\Models\User;
use App\Plugins\YourPlugin\Services\YourService;
use Tests\TestCase;

class YourServiceTest extends TestCase
{
    protected YourService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(YourService::class);
    }
    
    public function test_can_perform_action(): void
    {
        $user = User::factory()->create(['level' => 10]);
        
        $result = $this->service->performAction($user, [
            'value' => 100,
        ]);
        
        $this->assertTrue($result['success']);
    }
    
    public function test_cannot_perform_when_in_jail(): void
    {
        $user = User::factory()->create([
            'jail_until' => now()->addHours(1),
        ]);
        
        $result = $this->service->performAction($user, [
            'value' => 100,
        ]);
        
        $this->assertFalse($result['success']);
    }
}
```

---

## Next Steps

- [Hook System](Hook-System) - Inter-plugin communication
- [Models & Database](Models-and-Database) - Data layer
- [Creating Plugins](Creating-Plugins) - Full plugin example
