# Module Hook System Documentation

## Overview

The module hook system allows modules to dynamically register themselves and hook into game events without modifying core files. This creates a truly modular architecture similar to the legacy system.

## Features

### 1. Dynamic Navigation
Modules automatically appear on the player dashboard based on:
- Player level requirements
- Module enabled/disabled status
- Navigation configuration (color, icon, section)

### 2. Event Hooks
Modules can register hooks to respond to game events:
- `OnCrimeCommit` - When a crime is committed
- `OnCombat` - When combat occurs
- `OnLevelUp` - When a player levels up
- `OnPlayerLogin` - When a player logs in
- `OnPurchase` - When a player makes a purchase
- `OnTravel` - When a player travels

## Module Configuration

### Navigation Config Structure

Each module can define navigation configuration in the `navigation_config` JSON column:

```php
'navigation_config' => [
    'color' => 'bg-red-600',                    // Tailwind color class
    'gradient' => 'from-red-600 to-red-700',    // Gradient classes
    'section' => 'main',                        // Navigation section
    'icon_svg' => '<svg>...</svg>',             // SVG icon HTML
]
```

### Navigation Sections

Modules are grouped into sections on the dashboard:
- `main` - Primary game actions (crimes, combat, travel, etc.)
- `secondary` - Supporting features (hospital, bullets, gym, etc.)
- `features` - Advanced features (properties, detective, organized crime, etc.)

## Using the Hook System

### Registering Hooks

Modules can register hooks using the ModuleService:

```php
use App\Services\ModuleService;

$moduleService = app(ModuleService::class);

// Register hooks for the missions module
$moduleService->registerHooks('missions', [
    'OnCrimeCommit' => function($data) {
        // Update mission progress when crimes are committed
        $player = $data['player'];
        $crimeType = $data['crime_type'];
        
        // Check if player has an active crime mission
        $mission = PlayerMission::where('player_id', $player->id)
            ->where('status', 'active')
            ->whereHas('mission', function($q) use ($crimeType) {
                $q->where('objective_type', 'crime')
                  ->where('objective_data->crime_type', $crimeType);
            })
            ->first();
            
        if ($mission) {
            $mission->increment('progress');
        }
    },
    
    'OnLevelUp' => function($data) {
        // Unlock new missions when player levels up
        $player = $data['player'];
        $newLevel = $data['new_level'];
        
        // Check for missions that just became available
        // Notify player of new missions
    }
]);
```

### Firing Hooks

In your game controllers, fire hooks using the ModuleService:

```php
use App\Services\ModuleService;
use App\Events\Module\OnCrimeCommit;

class CrimeController extends Controller
{
    public function commit(Request $request, Crime $crime)
    {
        $player = $request->user()->player;
        
        // Execute crime logic
        $result = $this->crimeService->commit($player, $crime);
        
        // Fire hook event
        $moduleService = app(ModuleService::class);
        $event = new OnCrimeCommit(
            player: $player,
            crimeType: $crime->type,
            success: $result->success,
            cashEarned: $result->cash,
            respectEarned: $result->respect
        );
        
        // Execute all registered hooks
        $moduleService->executeHooks($event::getName(), $event->getData());
        
        return response()->json($result);
    }
}
```

## Available Hook Events

### OnCrimeCommit
Fired when a crime is committed.

**Data:**
- `player` - The player committing the crime
- `crime_type` - Type of crime (e.g., 'theft', 'murder')
- `success` - Whether the crime succeeded
- `cash_earned` - Cash earned from crime
- `respect_earned` - Respect earned from crime

**Example Use Cases:**
- Update mission progress
- Track crime statistics
- Award achievements
- Trigger gang reputation changes

### OnCombat
Fired when combat occurs between players.

**Data:**
- `attacker` - The attacking player
- `defender` - The defending player
- `attacker_won` - Whether the attacker won
- `damage_dealt` - Damage dealt in combat
- `cash_stolen` - Cash stolen (if any)

**Example Use Cases:**
- Update combat missions
- Track PvP statistics
- Award bounty rewards
- Update gang war progress

### OnLevelUp
Fired when a player levels up.

**Data:**
- `player` - The player who leveled up
- `old_level` - Previous level
- `new_level` - New level

**Example Use Cases:**
- Unlock new missions
- Grant level-up rewards
- Send notifications
- Update module accessibility

### OnPlayerLogin
Fired when a player logs in.

**Data:**
- `player` - The player logging in
- `ip_address` - Login IP address

**Example Use Cases:**
- Daily login rewards
- Reset daily missions
- Track login statistics
- Security logging

### OnPurchase
Fired when a player makes a purchase.

**Data:**
- `player` - The player making purchase
- `item_type` - Type of item (e.g., 'weapon', 'property')
- `item` - The purchased item
- `cost` - Purchase cost

**Example Use Cases:**
- Update shopping missions
- Track spending statistics
- Award purchase achievements
- Calculate economy metrics

### OnTravel
Fired when a player travels to a new location.

**Data:**
- `player` - The traveling player
- `from_location` - Starting location
- `to_location` - Destination location

**Example Use Cases:**
- Update travel missions
- Track exploration progress
- Unlock location-based content
- Award travel achievements

## Creating Custom Modules

### Step 1: Database Entry

Add your module to the `modules` table via seeder:

```php
[
    'name' => 'my_module',
    'display_name' => 'My Module',
    'description' => 'Description of my module',
    'icon' => '🎯',
    'route_name' => 'my-module.index',
    'enabled' => true,
    'order' => 21,
    'required_level' => 10,
    'navigation_config' => [
        'color' => 'bg-purple-600',
        'gradient' => 'from-purple-600 to-purple-700',
        'section' => 'features',
        'icon_svg' => '<svg>...</svg>',
    ],
]
```

### Step 2: Register Routes

Add routes in `routes/web.php`:

```php
Route::get('/my-module', [MyModuleController::class, 'index'])->name('my-module.index');
```

### Step 3: Create Controller

```php
<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MyModuleController extends Controller
{
    public function index(Request $request)
    {
        $player = $request->user()->player;
        
        // Module logic here
        
        return Inertia::render('MyModule/Index', [
            'player' => $player,
            'data' => $data
        ]);
    }
}
```

### Step 4: Register Hooks (Optional)

Register hooks in a service provider or module initializer:

```php
use App\Services\ModuleService;

public function boot()
{
    $moduleService = app(ModuleService::class);
    
    $moduleService->registerHooks('my_module', [
        'OnCrimeCommit' => function($data) {
            // Handle crime event
        },
        'OnLevelUp' => function($data) {
            // Handle level up event
        }
    ]);
}
```

### Step 5: Create Vue Component

Create `resources/js/Pages/MyModule/Index.vue`:

```vue
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    player: Object,
    data: Object
});
</script>

<template>
    <AppLayout title="My Module">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Module
            </h2>
        </template>

        <div class="py-12">
            <!-- Module content here -->
        </div>
    </AppLayout>
</template>
```

## Module Management

### Enabling/Disabling Modules

Modules can be enabled or disabled via the database:

```php
$module = Module::where('name', 'my_module')->first();
$module->enabled = false;
$module->save();
```

Disabled modules will not appear in navigation or be accessible to players.

### Level Requirements

Modules can require a minimum player level:

```php
$module->required_level = 15; // Only accessible at level 15+
$module->save();
```

### Module Order

Control the display order of modules:

```php
$module->order = 5; // Lower numbers appear first
$module->save();
```

## Best Practices

1. **Use Hooks Sparingly** - Only register hooks for events your module actually needs
2. **Keep Hooks Fast** - Hook callbacks should be lightweight; use queued jobs for heavy processing
3. **Handle Errors** - Wrap hook callbacks in try-catch to prevent one module from breaking others
4. **Document Hooks** - Document what hooks your module registers and why
5. **Test Independently** - Test your module with hooks disabled to ensure core functionality works
6. **Section Appropriately** - Use the correct navigation section (main/secondary/features) for your module
7. **Level Requirements** - Set appropriate level requirements to guide player progression

## Troubleshooting

### Module Not Appearing on Dashboard

1. Check if module is enabled: `SELECT * FROM modules WHERE name = 'my_module';`
2. Verify player meets level requirement
3. Check navigation_config is properly set
4. Clear cache and rebuild frontend: `npm run build`

### Hooks Not Firing

1. Verify hook is registered: Check module settings column
2. Ensure event is being fired in controller
3. Check hook callback doesn't have errors (check logs)
4. Verify ModuleService is being used correctly

### Navigation Config Not Updating

1. Re-run the module seeder: `php artisan db:seed --class=ModuleSeeder`
2. Clear application cache: `php artisan cache:clear`
3. Rebuild frontend: `npm run build`

## API Reference

### ModuleService Methods

#### getNavigationItems(Player $player): Collection
Returns grouped navigation items for a player, organized by section.

```php
$items = $moduleService->getNavigationItems($player);
// Returns: Collection with keys 'main', 'secondary', 'features'
```

#### registerHooks(string $moduleName, array $hooks): void
Register hook callbacks for a module.

```php
$moduleService->registerHooks('missions', [
    'OnCrimeCommit' => function($data) { /* ... */ }
]);
```

#### executeHooks(string $event, array $data): Collection
Execute all registered hooks for an event.

```php
$results = $moduleService->executeHooks('OnCrimeCommit', $eventData);
```

#### getModulesForPlayer(Player $player): Collection
Get all enabled modules accessible to a player.

```php
$modules = $moduleService->getModulesForPlayer($player);
```

#### canPlayerAccessModule(string $moduleName, Player $player): bool
Check if a player can access a specific module.

```php
if ($moduleService->canPlayerAccessModule('properties', $player)) {
    // Player can access properties
}
```

## Migration Guide from Hardcoded Navigation

If you have hardcoded module cards in Dashboard.vue, follow these steps:

1. Update ModuleSeeder with navigation_config for all modules
2. Run seeder: `php artisan db:seed --class=ModuleSeeder`
3. Update Dashboard controller to pass `navigationItems`
4. Replace hardcoded cards with dynamic v-for loops
5. Build frontend: `npm run build`
6. Test with different player levels

## Future Enhancements

Potential future additions to the hook system:
- Module dependencies (require other modules)
- Module conflicts (mutually exclusive modules)
- Hook priorities (control execution order)
- Async hooks with queues
- Module marketplace/packaging system
- Hot-reload module configuration
- Module-specific settings UI
- Inter-module communication API
