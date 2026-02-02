# NPC Combat System Setup

## Database Setup

1. Run the migration:
```bash
php artisan migrate
```

2. Seed the combat locations and enemies:
```bash
php artisan db:seed --class=CombatLocationsSeeder
```

## API Endpoints

### Get Locations
`GET /api/combat/locations`

Returns all available combat locations with their areas.

### Start Hunt (Spawn NPC)
`POST /api/combat/hunt`

Body:
```json
{
  "location_id": 1,
  "area_id": 1
}
```

Returns the enemy details, fight_id, player's weapons, and equipment.

### Attack NPC
`POST /api/combat/attack-npc`

Body:
```json
{
  "fight_id": 1,
  "weapon_id": 2  // optional, null for fists
}
```

Returns fight result, damage dealt, enemy/player health, and if fight ended.

### Auto Attack
`POST /api/combat/auto-attack-npc`

Body:
```json
{
  "fight_id": 1
}
```

Performs 3-5 automatic attacks and returns all logs.

## Frontend Flow

1. **Location Selection Screen**: User selects a location (ARCADE, CINEMA, etc.)
2. **Area Selection Screen**: User selects an area within the location (DARKENED RESTROOMS, etc.)
3. **Combat Screen**: 
   - Shows player stats and equipment
   - Shows enemy stats and health bar
   - Player can select weapons to attack
   - Fight timer counts down from 9:50
   - Fight log shows all combat actions
   - Ends when enemy dies, player dies, or timer expires

## Data Structure

### Combat Locations
- id, name, description, image, energy_cost, min_level, active, order

### Combat Areas
- id, location_id, name, description, difficulty (1-5 skulls), min_level, active

### Combat Enemies
- id, area_id, name, level, health, strength, defense, speed, agility, weakness
- experience_reward, cash_reward_min, cash_reward_max, spawn_rate, difficulty

### Combat Fights
- id, user_id, enemy_id, area_id, enemy_health, player_health_start
- started_at, expires_at (10 minutes), status (active/won/lost/fled)

### Combat Fight Logs
- id, fight_id, attacker_type (player/enemy), damage, critical, missed, weapon_used, message

## Adding New Content

### Add a New Location:
```php
CombatLocation::create([
    'name' => 'New Location',
    'description' => 'Description',
    'energy_cost' => 30,
    'min_level' => 20,
    'active' => true,
    'order' => 9,
]);
```

### Add a New Area:
```php
CombatArea::create([
    'location_id' => 1,
    'name' => 'Dangerous Room',
    'difficulty' => 3,
    'min_level' => 15,
    'active' => true,
]);
```

### Add a New Enemy:
```php
CombatEnemy::create([
    'area_id' => 1,
    'name' => 'Tough Enemy',
    'level' => 50,
    'health' => 2000,
    'max_health' => 2000,
    'strength' => 20000,
    'defense' => 100000,
    'speed' => 35000,
    'agility' => 50000,
    'weakness' => 'Fire',
    'difficulty' => 3,
    'experience_reward' => 1000,
    'cash_reward_min' => 500,
    'cash_reward_max' => 1500,
    'spawn_rate' => 1.0,
    'active' => true,
]);
```

## Notes

- Fights expire after 10 minutes
- Players can only have one active fight at a time
- Weapons that use ammo will consume bullets from player's inventory
- Equipment durability is tracked but not yet decreased (add this logic as needed)
- Enemy spawn rates allow for weighted random selection (0.01 to 1.00)
- Critical hits have a 10% chance and deal 2x damage
