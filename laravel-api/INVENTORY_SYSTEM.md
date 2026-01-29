# Inventory System - Complete Implementation

## Overview
A comprehensive player inventory system with equipment slots, item stats, buy/sell mechanics, and rarity tiers.

## Features Implemented

### 1. Database Schema
- **items table**: Stores all available items with stats, requirements, and properties
  - Types: weapon, armor, consumable, vehicle, misc
  - Rarity: common, uncommon, rare, epic, legendary
  - Stats system (JSON): damage, defense, speed, health effects
  - Requirements system (JSON): level, etc.
  - Stackable items with max_stack limits
  - Tradeable flag for marketplace control
  
- **player_inventories table**: Player-owned items with equipment tracking
  - quantity: Stack count for stackable items
  - equipped: Boolean flag
  - slot: Equipment slot (weapon/armor/vehicle)
  - Unique constraint on (player_id, item_id, slot)

### 2. Models & Relationships
- **Item Model** (`app/Models/Item.php`)
  - `inventories()` - HasMany to PlayerInventory
  - `canBeUsedBy($player)` - Validates requirements
  - `getRarityColorAttribute()` - UI helper for color coding

- **PlayerInventory Model** (`app/Models/PlayerInventory.php`)
  - `player()` - BelongsTo Player
  - `item()` - BelongsTo Item

- **Player Model** (Updated)
  - `inventory()` - HasMany PlayerInventory
  - `items()` - BelongsToMany Item with pivot data
  - `equippedItems()` - Filtered HasMany for equipped items only

### 3. Service Layer
**InventoryService** (`app/Services/InventoryService.php`) - Complete business logic:

- `getPlayerInventory($player)` - Fetch all inventory with item details
- `addItem($player, $itemId, $quantity)` - Add items with smart stacking
  - Validates requirements (level, etc.)
  - Handles stackable vs non-stackable items
  - Respects max_stack limits
  - Auto-stacks existing items
  
- `removeItem($player, $inventoryId, $quantity)` - Remove items
  - Prevents removing equipped items
  - Handles partial removal from stacks
  - Deletes inventory record when quantity reaches 0
  
- `equipItem($player, $inventoryId)` - Equipment system
  - Determines slot by item type (weapon/armor/vehicle)
  - Auto-unequips items in same slot
  - Validates requirements before equipping
  
- `unequipItem($player, $inventoryId)` - Remove equipment
  - Clears equipped flag and slot
  
- `useItem($player, $inventoryId)` - Consumable system
  - Only works on consumable type items
  - Applies stat effects (health, cash, etc.)
  - Removes 1 from quantity
  - Refreshes player stats
  
- `buyItem($player, $itemId, $quantity)` - Purchase system
  - Validates sufficient cash
  - Deducts cost (price * quantity)
  - Calls addItem to handle inventory
  
- `sellItem($player, $inventoryId, $quantity)` - Selling system
  - Validates item is tradeable
  - Prevents selling equipped items
  - Adds sell_price * quantity to cash
  - Calls removeItem to handle inventory

### 4. Controller Layer
**InventoryController** (`app/Http/Controllers/InventoryController.php`)

Routes handled:
- `GET /inventory` - Display inventory (index)
- `GET /shop` - Display available items (shop)
- `POST /shop/{item}/buy` - Purchase items
- `POST /inventory/{inventory}/sell` - Sell items
- `POST /inventory/{inventory}/equip` - Equip items
- `POST /inventory/{inventory}/unequip` - Unequip items
- `POST /inventory/{inventory}/use` - Use consumables

All actions wrapped in try/catch with flash messages.

### 5. Frontend Components

**Inventory Page** (`resources/js/Pages/Inventory/Index.vue`)
- Displays player's inventory in grid layout
- Equipped items section with special styling
- Filter tabs: All, Weapons, Armor, Vehicles, Consumables
- Item cards show:
  - Rarity bar (color-coded)
  - Name, type, description
  - Stats badges
  - Quantity
  - Sell value
- Actions per item:
  - Equip button (for weapons/armor/vehicles)
  - Use button (for consumables)
  - Sell button (if tradeable and not equipped)
- Links to Shop and Dashboard

**Shop Page** (`resources/js/Pages/Inventory/Shop.vue`)
- Displays all available items for purchase
- Filter tabs by item type
- Item cards show:
  - Locked overlay for items requiring higher level
  - Rarity indication
  - Stats preview
  - Requirements (with check/cross indicators)
  - Price calculator (quantity * unit price)
- Quantity selector for stackable items
- Buy button with affordability check
- Real-time validation (can afford, meets requirements)
- Links to Inventory and Dashboard

### 6. Admin Panel Integration
**ItemResource** (`app/Filament/Resources/ItemResource.php`)
- Full CRUD for items
- Navigation: System group
- Form sections:
  - Basic Information (name, type, description, image)
  - Pricing (price, sell_price)
  - Properties (tradeable, stackable, max_stack, rarity)
  - Stats & Requirements (KeyValue fields)
- Table with:
  - Image preview
  - Type badges (color-coded)
  - Rarity badges (color-coded)
  - Price display
  - Filters by type and rarity

### 7. Dashboard Integration
- Added "Inventory" module (🎒)
- Added "Shop" module (🛒)
- Both visible in Row 5 of dashboard
- Controlled by module access system

### 8. Seeded Data
**ItemSeeder** (`database/seeders/ItemSeeder.php`)

Weapons:
- Baseball Bat ($500, 15 damage, Level 1, Common)
- Pistol ($2,500, 30 damage, Level 5, Uncommon)
- Shotgun ($8,000, 60 damage, Level 15, Rare)
- AK-47 ($25,000, 100 damage, Level 30, Epic)

Armor:
- Leather Jacket ($1,000, 10 defense, Level 1, Common)
- Bulletproof Vest ($5,000, 30 defense, Level 10, Rare)
- Tactical Armor ($15,000, 50 defense, Level 25, Epic)

Vehicles:
- Motorcycle ($10,000, 20 speed, Level 5, Uncommon)
- Sports Car ($50,000, 50 speed, Level 20, Rare)
- Armored SUV ($200,000, 40 speed + 30 defense, Level 40, Legendary)

Consumables:
- First Aid Kit ($500, +50 health, stackable x10)
- Energy Drink ($200, +25 health, stackable x20)

## Routes Added
```php
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/shop', [InventoryController::class, 'shop'])->name('shop.index');
Route::post('/shop/{item}/buy', [InventoryController::class, 'buy'])->name('shop.buy');
Route::post('/inventory/{inventory}/sell', [InventoryController::class, 'sell'])->name('inventory.sell');
Route::post('/inventory/{inventory}/equip', [InventoryController::class, 'equip'])->name('inventory.equip');
Route::post('/inventory/{inventory}/unequip', [InventoryController::class, 'unequip'])->name('inventory.unequip');
Route::post('/inventory/{inventory}/use', [InventoryController::class, 'use'])->name('inventory.use');
```

## Modules Added
- inventory (Level 1, enabled)
- shop (Level 1, enabled)

## Technical Highlights

### Smart Stacking
- Stackable items combine automatically
- Respects max_stack limits
- Creates new inventory row if at max_stack

### Equipment System
- Slot-based (weapon, armor, vehicle)
- Auto-unequips when equipping to occupied slot
- Prevents actions on equipped items (sell, remove)
- Visual indication in UI

### Requirement Validation
- Level requirements checked before:
  - Adding items to inventory
  - Equipping items
  - Displaying in shop (with lock overlay)
- Extensible JSON format for future requirements

### Rarity System
- 5 tiers with color coding
- Visual bars on item cards
- Affects item value and appearance
- Filter-able in admin panel

### Economic Integration
- Buy: Deducts from player cash
- Sell: Adds to player cash (at sell_price)
- Validation prevents negative balances
- Different buy/sell prices (50% resale by default)

### User Experience
- Real-time affordability checking
- Locked items show requirements
- Quantity selectors for bulk buying
- Equipped items highlighted
- Filter system for easy navigation
- Responsive grid layouts

## Files Created/Modified

### Created:
- `database/migrations/2026_01_27_233917_create_items_table.php`
- `database/migrations/2026_01_27_233918_create_player_inventories_table.php`
- `app/Models/Item.php`
- `app/Models/PlayerInventory.php`
- `app/Services/InventoryService.php`
- `app/Http/Controllers/InventoryController.php`
- `database/seeders/ItemSeeder.php`
- `app/Filament/Resources/ItemResource.php`
- `resources/js/Pages/Inventory/Index.vue`
- `resources/js/Pages/Inventory/Shop.vue`

### Modified:
- `app/Models/Player.php` - Added inventory relationships
- `routes/web.php` - Added 7 inventory routes
- `database/seeders/ModuleSeeder.php` - Added inventory and shop modules
- `resources/js/Pages/Dashboard.vue` - Added inventory/shop links

## Testing Checklist
- [x] Migrations run successfully
- [x] Items seeded (12 items)
- [x] Modules seeded (inventory, shop)
- [x] Frontend builds without errors
- [x] Routes registered correctly
- [ ] Shop displays items (requires login)
- [ ] Purchase items with validation
- [ ] Inventory displays owned items
- [ ] Equip/unequip functionality
- [ ] Use consumables (health restoration)
- [ ] Sell items (cash received)
- [ ] Admin panel CRUD for items
- [ ] Level requirements enforced
- [ ] Stacking works correctly
- [ ] Equipment slots prevent duplicates

## Next Steps
1. Test complete user flow (buy → equip → use → sell)
2. Add combat integration (use equipped weapon/armor stats)
3. Add trading system (player-to-player)
4. Add item durability system
5. Add item crafting/upgrading
6. Add rare item drops from crimes
7. Add item sets with bonuses
8. Add inventory capacity limits

## Status
✅ **COMPLETE** - Backend, frontend, admin panel, and seeded data all implemented and built.
