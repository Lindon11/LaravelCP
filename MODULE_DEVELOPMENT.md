# LaravelCP Module Development Guide

## Table of Contents
1. [Introduction](#introduction)
2. [Module Structure](#module-structure)
3. [Creating a New Module](#creating-a-new-module)
4. [Module Configuration](#module-configuration)
5. [Controllers](#controllers)
6. [Models](#models)
7. [Routes](#routes)
8. [Frontend Views](#frontend-views)
9. [Database Migrations](#database-migrations)
10. [Hooks & Events](#hooks--events)
11. [Admin Panel Integration](#admin-panel-integration)
12. [Testing Your Module](#testing-your-module)
13. [Examples](#examples)

---

## Introduction

LaravelCP uses a modular architecture that allows developers to create self-contained game features. Each module can include its own controllers, models, routes, views, and database migrations.

### Benefits of Modular Architecture
- **Isolation**: Each module is self-contained and independent
- **Reusability**: Modules can be shared across projects
- **Maintainability**: Easy to update or remove features
- **Scalability**: Add new features without affecting existing code

---

## Module Structure

Every module follows this standardized structure:

```
app/Modules/YourModule/
├── YourModuleModule.php          # Main module class
├── Controllers/
│   └── YourModuleController.php  # API controller
├── module.json                   # Module configuration
├── hooks.php                     # Event hooks (optional)
├── routes/
│   └── web.php                   # Module routes
└── resources/
    └── views/
        └── Index.vue             # Frontend component (optional)
```

---

## Creating a New Module

### Step 1: Create Module Directory

Create your module directory structure:

```bash
mkdir -p app/Modules/MyNewModule/Controllers
mkdir -p app/Modules/MyNewModule/routes
mkdir -p app/Modules/MyNewModule/resources/views
```

### Step 2: Create the Main Module Class

Create `app/Modules/MyNewModule/MyNewModuleModule.php`:

```php
<?php

namespace App\Modules\MyNewModule;

use App\Modules\Module;

class MyNewModuleModule extends Module
{
    /**
     * Register module services
     */
    public function register(): void
    {
        // Register any services or bindings
    }

    /**
     * Boot module services
     */
    public function boot(): void
    {
        // Boot logic, load routes, views, etc.
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }

    /**
     * Get module configuration
     */
    public function getConfig(): array
    {
        return json_decode(
            file_get_contents(__DIR__ . '/module.json'),
            true
        );
    }
}
```

### Step 3: Create Module Configuration

Create `app/Modules/MyNewModule/module.json`:

```json
{
    "name": "mynewmodule",
    "display_name": "My New Module",
    "description": "Description of what your module does",
    "version": "1.0.0",
    "icon": "🎮",
    "author": "Your Name",
    "enabled": true,
    "required_level": 1,
    "order": 100,
    "routes": [
        {
            "name": "mynewmodule.index",
            "path": "/mynewmodule",
            "method": "GET",
            "description": "Main module page"
        }
    ],
    "permissions": [
        "mynewmodule.view",
        "mynewmodule.use"
    ],
    "settings": {
        "cooldown": 60,
        "energy_cost": 10,
        "max_attempts": 5
    }
}
```

---

## Module Configuration

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Unique identifier (lowercase, no spaces) |
| `display_name` | string | Human-readable name |
| `description` | string | Brief description of functionality |
| `version` | string | Semantic version (e.g., "1.0.0") |
| `icon` | string | Emoji or icon identifier |
| `enabled` | boolean | Whether module is active |
| `required_level` | integer | Minimum player level required |

### Optional Fields

| Field | Type | Description |
|-------|------|-------------|
| `order` | integer | Display order in navigation |
| `routes` | array | API route definitions |
| `permissions` | array | Required permissions |
| `settings` | object | Module-specific settings |
| `dependencies` | array | Other required modules |

---

## Controllers

Create your API controller in `app/Modules/MyNewModule/Controllers/MyNewModuleController.php`:

```php
<?php

namespace App\Modules\MyNewModule\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MyNewModuleController extends Controller
{
    /**
     * Display module data
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Your logic here
        $data = [
            'items' => [],
            'stats' => [],
            'available' => true
        ];
        
        return response()->json($data);
    }
    
    /**
     * Perform module action
     */
    public function action(Request $request): JsonResponse
    {
        $request->validate([
            'action_id' => 'required|integer|exists:module_actions,id'
        ]);
        
        $user = $request->user();
        
        // Check cooldown
        $timer = $user->timers()->where('type', 'mynewmodule')->first();
        if ($timer && $timer->expires_at > now()) {
            return response()->json([
                'success' => false,
                'message' => 'You must wait before doing this again.',
                'cooldown' => $timer->expires_at->diffInSeconds(now())
            ], 429);
        }
        
        // Check energy
        if ($user->energy < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough energy.'
            ], 400);
        }
        
        // Perform action
        $user->decrement('energy', 10);
        
        // Set cooldown
        $user->timers()->updateOrCreate(
            ['user_id' => $user->id, 'type' => 'mynewmodule'],
            ['expires_at' => now()->addSeconds(60)]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Action completed successfully!',
            'reward' => 100
        ]);
    }
}
```

---

## Models

If your module needs database tables, create models in `app/Models/`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MyModuleAction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'energy_cost',
        'cooldown',
        'min_reward',
        'max_reward',
        'required_level'
    ];

    protected $casts = [
        'energy_cost' => 'integer',
        'cooldown' => 'integer',
        'min_reward' => 'integer',
        'max_reward' => 'integer',
        'required_level' => 'integer'
    ];
    
    /**
     * Get users who have completed this action
     */
    public function attempts()
    {
        return $this->hasMany(MyModuleAttempt::class);
    }
}
```

---

## Routes

Define your API routes in `app/Modules/MyNewModule/routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MyNewModule\Controllers\MyNewModuleController;

Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
    Route::prefix('mynewmodule')->group(function () {
        // GET /api/mynewmodule
        Route::get('/', [MyNewModuleController::class, 'index']);
        
        // POST /api/mynewmodule/action
        Route::post('/action', [MyNewModuleController::class, 'action']);
        
        // GET /api/mynewmodule/stats
        Route::get('/stats', [MyNewModuleController::class, 'stats']);
        
        // GET /api/mynewmodule/history
        Route::get('/history', [MyNewModuleController::class, 'history']);
    });
});
```

---

## Frontend Views

Create a Vue component in `app/Modules/MyNewModule/resources/views/Index.vue`:

```vue
<template>
  <div class="module-container">
    <h1>{{ moduleData.display_name }}</h1>
    
    <div v-if="loading" class="loading">
      Loading...
    </div>
    
    <div v-else>
      <!-- Module content -->
      <div class="actions-grid">
        <div 
          v-for="action in actions" 
          :key="action.id"
          class="action-card"
          @click="performAction(action.id)"
        >
          <h3>{{ action.name }}</h3>
          <p>{{ action.description }}</p>
          <div class="stats">
            <span>⚡ {{ action.energy_cost }} Energy</span>
            <span>💰 ${{ action.min_reward }}-${{ action.max_reward }}</span>
          </div>
        </div>
      </div>
      
      <!-- Cooldown timer -->
      <div v-if="cooldown > 0" class="cooldown">
        Next action available in: {{ formatTime(cooldown) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const loading = ref(true)
const actions = ref([])
const cooldown = ref(0)
const moduleData = ref({
  display_name: 'My New Module'
})

const loadData = async () => {
  try {
    const response = await api.get('/mynewmodule')
    actions.value = response.data.items
    cooldown.value = response.data.cooldown || 0
  } catch (error) {
    console.error('Failed to load module data:', error)
  } finally {
    loading.value = false
  }
}

const performAction = async (actionId) => {
  if (cooldown.value > 0) {
    alert('Please wait before performing another action')
    return
  }
  
  try {
    const response = await api.post('/mynewmodule/action', {
      action_id: actionId
    })
    
    if (response.data.success) {
      alert(response.data.message)
      cooldown.value = response.data.cooldown || 60
      await loadData()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Action failed')
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

onMounted(() => {
  loadData()
  
  // Update cooldown timer
  const interval = setInterval(() => {
    if (cooldown.value > 0) {
      cooldown.value--
    }
  }, 1000)
  
  return () => clearInterval(interval)
})
</script>

<style scoped>
.module-container {
  padding: 2rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin: 2rem 0;
}

.action-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.action-card:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
}

.stats {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.cooldown {
  text-align: center;
  padding: 1rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 0.5rem;
  color: #ef4444;
  font-weight: 600;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}
</style>
```

---

## Database Migrations

Create migrations for your module tables:

```bash
php artisan make:migration create_mynewmodule_tables
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mynewmodule_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('energy_cost')->default(10);
            $table->integer('cooldown')->default(60);
            $table->integer('min_reward')->default(50);
            $table->integer('max_reward')->default(150);
            $table->integer('required_level')->default(1);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('mynewmodule_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('action_id')->constrained('mynewmodule_actions');
            $table->boolean('success')->default(false);
            $table->integer('reward')->default(0);
            $table->timestamp('attempted_at');
            $table->timestamps();
            
            $table->index(['user_id', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mynewmodule_attempts');
        Schema::dropIfExists('mynewmodule_actions');
    }
};
```

---

## Hooks & Events

Use hooks to integrate with other modules. Create `app/Modules/MyNewModule/hooks.php`:

```php
<?php

use App\Facades\Hook;

// Listen to player level up
Hook::listen('player.levelup', function ($user, $newLevel) {
    // Grant rewards or unlock features
    if ($newLevel >= 10) {
        // Unlock special feature
    }
});

// Listen to combat events
Hook::listen('combat.won', function ($attacker, $defender) {
    // Award bonus for combat wins
    $bonus = rand(10, 50);
    $attacker->increment('cash', $bonus);
});

// Listen to crime events
Hook::listen('crime.committed', function ($user, $crime, $success) {
    if ($success) {
        // Track successful crimes for achievements
    }
});

// Fire your own events
Hook::fire('mynewmodule.action.completed', [
    'user' => $user,
    'action' => $action,
    'reward' => $reward
]);
```

### Available Hooks

| Hook Name | Parameters | Description |
|-----------|------------|-------------|
| `player.levelup` | `($user, $newLevel)` | Player gains a level |
| `combat.won` | `($attacker, $defender)` | Combat victory |
| `combat.lost` | `($attacker, $defender)` | Combat defeat |
| `crime.committed` | `($user, $crime, $success)` | Crime attempt |
| `player.login` | `($user)` | Player logs in |
| `item.purchased` | `($user, $item, $quantity)` | Item bought |
| `travel.completed` | `($user, $from, $to)` | Travel between cities |

---

## Admin Panel Integration

### Create Admin Controller

Create `app/Http/Controllers/Admin/MyNewModuleController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MyModuleAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MyNewModuleController extends Controller
{
    public function index(): JsonResponse
    {
        $actions = MyModuleAction::orderBy('name')->get();
        
        return response()->json([
            'actions' => $actions
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'energy_cost' => 'required|integer|min:1',
            'cooldown' => 'required|integer|min:0',
            'min_reward' => 'required|integer|min:0',
            'max_reward' => 'required|integer|min:0',
            'required_level' => 'required|integer|min:1'
        ]);
        
        $action = MyModuleAction::create($validated);
        
        return response()->json([
            'success' => true,
            'action' => $action
        ], 201);
    }
    
    public function update(Request $request, MyModuleAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'energy_cost' => 'integer|min:1',
            'cooldown' => 'integer|min:0',
            'min_reward' => 'integer|min:0',
            'max_reward' => 'integer|min:0',
            'required_level' => 'integer|min:1',
            'enabled' => 'boolean'
        ]);
        
        $action->update($validated);
        
        return response()->json([
            'success' => true,
            'action' => $action
        ]);
    }
    
    public function destroy(MyModuleAction $action): JsonResponse
    {
        $action->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Action deleted successfully'
        ]);
    }
}
```

### Add Admin Routes

Add to `routes/api.php`:

```php
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('mynewmodule', MyNewModuleController::class);
});
```

---

## Testing Your Module

### Register Module in Database

```sql
INSERT INTO modules (name, display_name, description, icon, enabled, required_level, created_at, updated_at)
VALUES ('mynewmodule', 'My New Module', 'Description here', '🎮', 1, 1, NOW(), NOW());
```

Or use Tinker:

```bash
php artisan tinker
```

```php
DB::table('modules')->insert([
    'name' => 'mynewmodule',
    'display_name' => 'My New Module',
    'description' => 'Description here',
    'icon' => '🎮',
    'enabled' => true,
    'required_level' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);
```

### Add Frontend Route

In OpenPBBG's `src/router/index.ts`, add:

```typescript
{
  path: '/mynewmodule',
  name: 'mynewmodule',
  component: () => import('@/views/modules/MyNewModuleView.vue'),
  meta: { requiresAuth: true }
}
```

### Test API Endpoints

```bash
# Test index endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8001/api/mynewmodule

# Test action endpoint
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"action_id": 1}' \
  http://localhost:8001/api/mynewmodule/action
```

---

## Examples

### Example 1: Simple Lottery Module

Minimal example showing basic module structure:

```php
// app/Modules/Lottery/Controllers/LotteryController.php
class LotteryController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'ticket_price' => 100,
            'jackpot' => 10000,
            'my_tickets' => $request->user()->lotteryTickets()->count()
        ]);
    }
    
    public function buyTicket(Request $request)
    {
        $user = $request->user();
        
        if ($user->cash < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough cash'
            ], 400);
        }
        
        $user->decrement('cash', 100);
        $user->lotteryTickets()->create([
            'number' => rand(1000, 9999)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Ticket purchased!'
        ]);
    }
}
```

### Example 2: Mining Module with Cooldown

Shows timer management and rewards:

```php
class MiningController extends Controller
{
    public function mine(Request $request)
    {
        $user = $request->user();
        
        // Check cooldown
        $canMine = $user->canPerformAction('mining', 300); // 5 min cooldown
        if (!$canMine) {
            return response()->json([
                'success' => false,
                'message' => 'You are too tired to mine',
                'cooldown' => $user->getActionCooldown('mining')
            ], 429);
        }
        
        // Check energy
        if ($user->energy < 20) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough energy'
            ], 400);
        }
        
        // Mine!
        $user->decrement('energy', 20);
        $ore = rand(1, 10);
        $value = $ore * rand(50, 100);
        
        $user->increment('cash', $value);
        $user->setActionCooldown('mining', 300);
        
        return response()->json([
            'success' => true,
            'message' => "You mined {$ore} ore worth \${$value}!",
            'ore' => $ore,
            'value' => $value
        ]);
    }
}
```

### Example 3: Training Module with Skill Progression

Shows stat increases and progression:

```php
class TrainingController extends Controller
{
    public function train(Request $request)
    {
        $request->validate([
            'skill' => 'required|in:strength,defense,speed,intelligence'
        ]);
        
        $user = $request->user();
        $skill = $request->skill;
        
        // Check requirements
        if ($user->energy < 15) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough energy'
            ], 400);
        }
        
        // Calculate gains
        $user->decrement('energy', 15);
        $gain = rand(1, 5);
        $user->increment($skill, $gain);
        $user->increment('experience', 10);
        
        // Check for level up
        if ($user->experience >= $user->next_level_exp) {
            $user->increment('level');
            $user->experience = 0;
            $user->next_level_exp = $user->level * 100;
            
            return response()->json([
                'success' => true,
                'message' => "You gained {$gain} {$skill}! Level up!",
                'level_up' => true,
                'new_level' => $user->level
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => "You gained {$gain} {$skill}!",
            'gain' => $gain,
            'new_value' => $user->$skill
        ]);
    }
}
```

---

## Best Practices

### 1. **Always Validate Input**
```php
$request->validate([
    'action_id' => 'required|integer|exists:actions,id'
]);
```

### 2. **Check User Requirements**
- Energy
- Cash
- Level
- Cooldowns
- Permissions

### 3. **Use Transactions for Critical Operations**
```php
DB::transaction(function () use ($user, $amount) {
    $user->decrement('cash', $amount);
    $user->increment('bank', $amount);
});
```

### 4. **Log Important Actions**
```php
ActivityLog::create([
    'user_id' => $user->id,
    'action' => 'module.action',
    'details' => json_encode(['action' => 'completed', 'reward' => 100])
]);
```

### 5. **Fire Events for Integration**
```php
Hook::fire('mynewmodule.action.completed', [
    'user' => $user,
    'reward' => $reward
]);
```

### 6. **Return Consistent JSON Responses**
```php
// Success
return response()->json([
    'success' => true,
    'message' => 'Action completed',
    'data' => $data
]);

// Error
return response()->json([
    'success' => false,
    'message' => 'Error description'
], 400);
```

---

## Troubleshooting

### Module Not Showing Up
1. Check if module is registered in database: `SELECT * FROM modules WHERE name='yourmodule'`
2. Verify `enabled = 1` in database
3. Check user's level meets `required_level`
4. Clear cache: `php artisan cache:clear`

### Routes Not Working
1. Verify routes file is loaded in main module class
2. Check route prefix and middleware
3. Run `php artisan route:list` to see all routes

### Frontend Not Loading
1. Ensure Vue component is in correct location
2. Check router configuration in OpenPBBG
3. Verify API endpoint returns data
4. Check browser console for errors

---

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org)
- [LaravelCP API Reference](API.md)
- [Module Hook System](MODULE_HOOK_SYSTEM.md)

---

## Support

For questions or issues:
- GitHub Issues: https://github.com/Lindon11/LaravelCP/issues
- Documentation: Check other .md files in repository root

---

**Last Updated**: February 2026
**Version**: 1.0.0
