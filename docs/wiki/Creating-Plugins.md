# Creating Plugins

Step-by-step guide to creating your own LaravelCP plugin.

---

## Quick Start

### 1. Create Plugin Directory Structure

```bash
mkdir -p app/Plugins/MyPlugin/{Controllers,Models,Services,routes,resources/views,database/migrations}
```

### 2. Create `plugin.json`

```json
{
    "name": "My Plugin",
    "slug": "my-plugin",
    "version": "1.0.0",
    "description": "My custom game feature",
    "author": "Your Name",
    "enabled": true,
    "requires": {
        "laravel": "^11.0",
        "modules": []
    },
    "settings": {
        "icon": "🎮",
        "color": "blue",
        "route": "my-plugin.index",
        "menu": {
            "enabled": true,
            "order": 100,
            "section": "actions",
            "parent": null
        },
        "permissions": {
            "view": "level:1",
            "use": "level:1"
        }
    },
    "hooks": {},
    "routes": {
        "web": true,
        "api": true,
        "admin": true
    }
}
```

### 3. Create Main Module Class

**`app/Plugins/MyPlugin/MyPluginModule.php`**

```php
<?php

namespace App\Plugins\MyPlugin;

use App\Plugins\Plugin;
use App\Core\Models\User;

class MyPluginModule extends Plugin
{
    protected string $name = 'MyPlugin';
    
    protected array $config = [];
    
    public function construct(): void
    {
        $this->config = [
            'cooldown' => 300,    // 5 minutes
            'energy_cost' => 10,
            'base_reward' => 100,
        ];
    }
    
    /**
     * Check if user can access this plugin
     */
    public function canAccess(User $user): bool
    {
        return $user->level >= 1 && !$user->isJailed();
    }
    
    /**
     * Get main plugin data
     */
    public function getData(User $user): array
    {
        return [
            'cooldown' => $user->getTimer('my_plugin'),
            'energy' => $user->energy,
            'can_use' => $this->canUse($user),
        ];
    }
    
    /**
     * Check if user can use the feature
     */
    public function canUse(User $user): bool
    {
        return !$user->hasTimer('my_plugin') 
            && $user->energy >= $this->config['energy_cost'];
    }
    
    /**
     * Execute the main action
     */
    public function execute(User $user): array
    {
        if (!$this->canUse($user)) {
            return ['success' => false, 'message' => 'Cannot use at this time'];
        }
        
        // Deduct energy
        $user->energy -= $this->config['energy_cost'];
        
        // Calculate reward
        $reward = rand(
            $this->config['base_reward'] * 0.5,
            $this->config['base_reward'] * 1.5
        );
        
        // Give reward
        $user->cash += $reward;
        $user->save();
        
        // Set cooldown
        $user->setTimer('my_plugin', $this->config['cooldown']);
        
        // Track action
        $this->trackAction('execute', [
            'reward' => $reward,
        ]);
        
        return [
            'success' => true,
            'message' => "You earned \${$reward}!",
            'reward' => $reward,
            'new_balance' => $user->cash,
        ];
    }
}
```

---

## Complete Plugin Example

Let's build a full **"Hacking"** plugin.

### Directory Structure

```text
app/Plugins/Hacking/
├── plugin.json
├── HackingModule.php
├── hooks.php
├── Controllers/
│   ├── HackingController.php
│   └── HackingManagementController.php
├── Models/
│   ├── HackTarget.php
│   └── HackAttempt.php
├── Services/
│   └── HackingService.php
├── routes/
│   ├── api.php
│   └── admin.php
└── database/
    └── migrations/
        └── 2024_01_01_000000_create_hacking_tables.php
```

### plugin.json

```json
{
    "name": "Hacking",
    "slug": "hacking",
    "version": "1.0.0",
    "description": "Hack corporations and players for data and cash",
    "author": "Your Name",
    "enabled": true,
    "requires": {
        "laravel": "^11.0",
        "modules": []
    },
    "settings": {
        "icon": "💻",
        "color": "green",
        "route": "hacking.index",
        "menu": {
            "enabled": true,
            "order": 15,
            "section": "actions",
            "parent": null
        },
        "permissions": {
            "view": "level:5",
            "use": "level:5"
        }
    },
    "hooks": {
        "afterHack": true,
        "alterHackReward": true
    },
    "routes": {
        "web": false,
        "api": true,
        "admin": true
    }
}
```

### Migration

**`database/migrations/2024_01_01_000000_create_hacking_tables.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hack targets (corporations/systems to hack)
        Schema::create('hack_targets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('difficulty')->default(1);  // 1-10
            $table->integer('min_reward');
            $table->integer('max_reward');
            $table->integer('experience_reward')->default(10);
            $table->integer('required_level')->default(5);
            $table->integer('cooldown')->default(300);  // seconds
            $table->integer('energy_cost')->default(15);
            $table->float('success_rate')->default(0.5);  // base rate
            $table->float('jail_chance')->default(0.1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        // Hack attempts (history)
        Schema::create('hack_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hack_target_id')->constrained()->cascadeOnDelete();
            $table->boolean('success');
            $table->integer('reward')->nullable();
            $table->integer('experience')->nullable();
            $table->boolean('was_caught')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('hack_attempts');
        Schema::dropIfExists('hack_targets');
    }
};
```

### Model: HackTarget

**`Models/HackTarget.php`**

```php
<?php

namespace App\Plugins\Hacking\Models;

use Illuminate\Database\Eloquent\Model;

class HackTarget extends Model
{
    protected $fillable = [
        'name',
        'description',
        'difficulty',
        'min_reward',
        'max_reward',
        'experience_reward',
        'required_level',
        'cooldown',
        'energy_cost',
        'success_rate',
        'jail_chance',
        'active',
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'success_rate' => 'float',
        'jail_chance' => 'float',
    ];
    
    public function attempts()
    {
        return $this->hasMany(HackAttempt::class);
    }
    
    /**
     * Get available targets for a user
     */
    public static function getAvailableFor($user)
    {
        return static::where('active', true)
            ->where('required_level', '<=', $user->level)
            ->orderBy('difficulty')
            ->get();
    }
}
```

### Model: HackAttempt

**`Models/HackAttempt.php`**

```php
<?php

namespace App\Plugins\Hacking\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class HackAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'hack_target_id',
        'success',
        'reward',
        'experience',
        'was_caught',
    ];
    
    protected $casts = [
        'success' => 'boolean',
        'was_caught' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function target()
    {
        return $this->belongsTo(HackTarget::class, 'hack_target_id');
    }
}
```

### Service: HackingService

**`Services/HackingService.php`**

```php
<?php

namespace App\Plugins\Hacking\Services;

use App\Core\Models\User;
use App\Plugins\Hacking\Models\HackTarget;
use App\Plugins\Hacking\Models\HackAttempt;
use App\Facades\Hook;

class HackingService
{
    /**
     * Calculate success rate for a hack attempt
     */
    public function calculateSuccessRate(HackTarget $target, User $user): float
    {
        $baseRate = $target->success_rate;
        
        // Level bonus (1% per level above requirement)
        $levelBonus = max(0, ($user->level - $target->required_level) * 0.01);
        
        // Intelligence/skill modifier (if you have such stats)
        // $skillBonus = $user->intelligence * 0.005;
        
        $rate = min(0.95, $baseRate + $levelBonus);
        
        // Apply hook for custom modifiers
        $rate = Hook::filter('hackSuccessRate', $rate, [
            'target' => $target,
            'user' => $user,
        ]);
        
        return $rate;
    }
    
    /**
     * Attempt to hack a target
     */
    public function attemptHack(User $user, HackTarget $target): array
    {
        // Validate
        if ($user->level < $target->required_level) {
            return ['success' => false, 'error' => 'Level requirement not met'];
        }
        
        if ($user->energy < $target->energy_cost) {
            return ['success' => false, 'error' => 'Not enough energy'];
        }
        
        if ($user->hasTimer('hack_' . $target->id)) {
            return ['success' => false, 'error' => 'Target is on cooldown'];
        }
        
        // Deduct energy
        $user->energy -= $target->energy_cost;
        
        // Calculate outcome
        $successRate = $this->calculateSuccessRate($target, $user);
        $roll = mt_rand(1, 100) / 100;
        $success = $roll <= $successRate;
        
        $result = [
            'success' => $success,
            'reward' => 0,
            'experience' => 0,
            'was_caught' => false,
        ];
        
        if ($success) {
            // Calculate reward
            $reward = rand($target->min_reward, $target->max_reward);
            $experience = $target->experience_reward;
            
            // Apply hook
            $reward = Hook::filter('alterHackReward', $reward, [
                'target' => $target,
                'user' => $user,
            ]);
            
            $user->cash += $reward;
            $user->experience += $experience;
            
            $result['reward'] = $reward;
            $result['experience'] = $experience;
            $result['message'] = "Hack successful! You stole \${$reward} and gained {$experience} EXP";
        } else {
            // Check if caught
            $caughtRoll = mt_rand(1, 100) / 100;
            if ($caughtRoll <= $target->jail_chance) {
                $jailTime = $target->difficulty * 60; // 1 min per difficulty
                $user->jail_until = now()->addSeconds($jailTime);
                $result['was_caught'] = true;
                $result['message'] = "Hack failed! You were caught and sent to jail for " . ($jailTime / 60) . " minutes";
            } else {
                $result['message'] = "Hack failed, but you escaped detection";
            }
        }
        
        $user->save();
        
        // Set cooldown
        $user->setTimer('hack_' . $target->id, $target->cooldown);
        
        // Record attempt
        HackAttempt::create([
            'user_id' => $user->id,
            'hack_target_id' => $target->id,
            'success' => $success,
            'reward' => $result['reward'],
            'experience' => $result['experience'],
            'was_caught' => $result['was_caught'],
        ]);
        
        // Fire hook
        Hook::action('afterHack', [
            'user' => $user,
            'target' => $target,
            'result' => $result,
        ]);
        
        return $result;
    }
}
```

### Controller: HackingController

**`Controllers/HackingController.php`**

```php
<?php

namespace App\Plugins\Hacking\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Hacking\Models\HackTarget;
use App\Plugins\Hacking\Services\HackingService;
use Illuminate\Http\Request;

class HackingController extends Controller
{
    protected HackingService $hackingService;
    
    public function __construct(HackingService $hackingService)
    {
        $this->hackingService = $hackingService;
    }
    
    /**
     * Get available hack targets
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $targets = HackTarget::getAvailableFor($user)
            ->map(function ($target) use ($user) {
                return [
                    'id' => $target->id,
                    'name' => $target->name,
                    'description' => $target->description,
                    'difficulty' => $target->difficulty,
                    'min_reward' => $target->min_reward,
                    'max_reward' => $target->max_reward,
                    'experience_reward' => $target->experience_reward,
                    'energy_cost' => $target->energy_cost,
                    'success_rate' => round($this->hackingService->calculateSuccessRate($target, $user) * 100) . '%',
                    'cooldown' => $user->getTimer('hack_' . $target->id),
                    'can_attempt' => !$user->hasTimer('hack_' . $target->id) && $user->energy >= $target->energy_cost,
                ];
            });
        
        return response()->json([
            'targets' => $targets,
            'user_energy' => $user->energy,
        ]);
    }
    
    /**
     * Attempt a hack
     */
    public function attempt(Request $request, $targetId)
    {
        $request->validate([
            // Add any validation rules
        ]);
        
        $user = $request->user();
        $target = HackTarget::findOrFail($targetId);
        
        $result = $this->hackingService->attemptHack($user, $target);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 400);
        }
        
        return response()->json($result);
    }
    
    /**
     * Get user's hack history
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        $attempts = $user->hackAttempts()
            ->with('target')
            ->latest()
            ->take(50)
            ->get();
        
        return response()->json(['history' => $attempts]);
    }
}
```

### Admin Controller: HackingManagementController

**`Controllers/HackingManagementController.php`**

```php
<?php

namespace App\Plugins\Hacking\Controllers;

use App\Http\Controllers\Controller;
use App\Plugins\Hacking\Models\HackTarget;
use App\Plugins\Hacking\Models\HackAttempt;
use Illuminate\Http\Request;

class HackingManagementController extends Controller
{
    /**
     * List all hack targets
     */
    public function index()
    {
        $targets = HackTarget::withCount('attempts')->get();
        
        return response()->json(['data' => $targets]);
    }
    
    /**
     * Create a new hack target
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|integer|min:1|max:10',
            'min_reward' => 'required|integer|min:0',
            'max_reward' => 'required|integer|min:0|gte:min_reward',
            'experience_reward' => 'required|integer|min:0',
            'required_level' => 'required|integer|min:1',
            'cooldown' => 'required|integer|min:0',
            'energy_cost' => 'required|integer|min:0',
            'success_rate' => 'required|numeric|min:0|max:1',
            'jail_chance' => 'required|numeric|min:0|max:1',
            'active' => 'boolean',
        ]);
        
        $target = HackTarget::create($validated);
        
        return response()->json([
            'message' => 'Hack target created',
            'data' => $target,
        ], 201);
    }
    
    /**
     * Update a hack target
     */
    public function update(Request $request, $id)
    {
        $target = HackTarget::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'integer|min:1|max:10',
            'min_reward' => 'integer|min:0',
            'max_reward' => 'integer|min:0',
            'experience_reward' => 'integer|min:0',
            'required_level' => 'integer|min:1',
            'cooldown' => 'integer|min:0',
            'energy_cost' => 'integer|min:0',
            'success_rate' => 'numeric|min:0|max:1',
            'jail_chance' => 'numeric|min:0|max:1',
            'active' => 'boolean',
        ]);
        
        $target->update($validated);
        
        return response()->json([
            'message' => 'Hack target updated',
            'data' => $target,
        ]);
    }
    
    /**
     * Delete a hack target
     */
    public function destroy($id)
    {
        $target = HackTarget::findOrFail($id);
        $target->delete();
        
        return response()->json([
            'message' => 'Hack target deleted',
        ]);
    }
    
    /**
     * Get statistics
     */
    public function statistics()
    {
        return response()->json([
            'total_attempts' => HackAttempt::count(),
            'successful_attempts' => HackAttempt::where('success', true)->count(),
            'total_stolen' => HackAttempt::where('success', true)->sum('reward'),
            'players_caught' => HackAttempt::where('was_caught', true)->count(),
        ]);
    }
}
```

### Routes

**`routes/api.php`**

```php
<?php

use App\Plugins\Hacking\Controllers\HackingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('hacking')->group(function () {
    Route::get('/', [HackingController::class, 'index']);
    Route::post('/{targetId}/attempt', [HackingController::class, 'attempt']);
    Route::get('/history', [HackingController::class, 'history']);
});
```

**`routes/admin.php`**

```php
<?php

use App\Plugins\Hacking\Controllers\HackingManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('hack-targets')->group(function () {
    Route::get('/', [HackingManagementController::class, 'index']);
    Route::post('/', [HackingManagementController::class, 'store']);
    Route::get('/statistics', [HackingManagementController::class, 'statistics']);
    Route::patch('/{id}', [HackingManagementController::class, 'update']);
    Route::delete('/{id}', [HackingManagementController::class, 'destroy']);
});
```

### Hooks

**`hooks.php`**

```php
<?php

use App\Facades\Hook;

// Add hacking to navigation
Hook::register('customMenus', function ($user) {
    if (!$user || $user->level < 5) return [];
    
    return [
        'hacking' => [
            'title' => 'Actions',
            'items' => [
                [
                    'url' => '/hacking',
                    'text' => 'Hacking',
                    'icon' => '💻',
                    'timer' => null,  // Could show next available target
                    'badge' => null,
                    'sort' => 150,
                ],
            ],
        ],
    ];
}, 10);

// Track hacking for achievements
Hook::register('afterHack', function ($data) {
    if ($data['result']['success']) {
        // Trigger achievement check
        event(new \App\Events\Module\OnModuleAction(
            $data['user'],
            'hacking',
            'hack_success',
            $data['result']['reward']
        ));
    }
}, 10);
```

---

## Installing Your Plugin

1. **Run migrations:**

   ```bash
   php artisan migrate
   ```

1. **Clear caches:**

   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

1. **Test the plugin:**

   ```bash
   # List routes
   php artisan route:list | grep hacking
   ```

---

## Best Practices

1. **Always validate input** in controllers
2. **Use services** for business logic
3. **Apply hooks** for extensibility
4. **Track actions** for analytics
5. **Handle errors gracefully**
6. **Document your plugin.json** thoroughly
7. **Write migrations** for all database changes
8. **Use timers** for cooldowns
9. **Check permissions** in `canAccess()`
10. **Test thoroughly** before deployment

---

## Next Steps

- [Hook System](Hook-System) - Add inter-plugin communication
- [Routes & Controllers](Routes-and-Controllers) - Advanced routing
- [Models & Database](Models-and-Database) - Database design
