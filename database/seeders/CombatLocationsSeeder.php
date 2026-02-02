<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CombatLocation;
use App\Models\CombatArea;
use App\Models\CombatEnemy;

class CombatLocationsSeeder extends Seeder
{
    public function run(): void
    {
        // ARCADE
        $arcade = CombatLocation::create([
            'name' => 'Arcade',
            'description' => 'A dimly lit arcade filled with broken machines',
            'energy_cost' => 20,
            'min_level' => 1,
            'active' => true,
            'order' => 1,
        ]);

        $darkRestrooms = CombatArea::create([
            'location_id' => $arcade->id,
            'name' => 'Darkened Restrooms',
            'description' => 'Dangerous area behind the arcade',
            'difficulty' => 1,
            'min_level' => 1,
            'active' => true,
        ]);

        CombatEnemy::create([
            'area_id' => $darkRestrooms->id,
            'name' => 'Frenzied Spitter',
            'description' => 'An aggressive junkie',
            'level' => 44,
            'health' => 1440,
            'max_health' => 1440,
            'strength' => 13698,
            'defense' => 80666,
            'speed' => 27300,
            'agility' => 40750,
            'weakness' => 'Piercing',
            'difficulty' => 1,
            'experience_reward' => 500,
            'cash_reward_min' => 100,
            'cash_reward_max' => 500,
            'spawn_rate' => 1.0,
            'active' => true,
        ]);

        $arcadeOffice = CombatArea::create([
            'location_id' => $arcade->id,
            'name' => 'Arcade Office',
            'description' => 'The manager\'s office',
            'difficulty' => 1,
            'min_level' => 1,
            'active' => true,
        ]);

        // CINEMA
        $cinema = CombatLocation::create([
            'name' => 'Cinema',
            'description' => 'An abandoned movie theater',
            'energy_cost' => 25,
            'min_level' => 5,
            'active' => true,
            'order' => 2,
        ]);

        $concessionStand = CombatArea::create([
            'location_id' => $cinema->id,
            'name' => 'Concession Stand',
            'description' => 'Old popcorn and danger',
            'difficulty' => 1,
            'min_level' => 5,
            'active' => true,
        ]);

        $hallOfMirrors = CombatArea::create([
            'location_id' => $cinema->id,
            'name' => 'Hall of Mirrors',
            'description' => 'Confusing and deadly',
            'difficulty' => 2,
            'min_level' => 5,
            'active' => true,
        ]);

        // SHOPPING MALL
        $mall = CombatLocation::create([
            'name' => 'Shopping Mall',
            'description' => 'A derelict shopping center',
            'energy_cost' => 25,
            'min_level' => 10,
            'active' => true,
            'order' => 3,
        ]);

        // WAREHOUSE
        $warehouse = CombatLocation::create([
            'name' => 'Warehouse',
            'description' => 'Industrial danger zone',
            'energy_cost' => 25,
            'min_level' => 15,
            'active' => true,
            'order' => 4,
        ]);

        // RESTAURANT
        $restaurant = CombatLocation::create([
            'name' => 'Restaurant',
            'description' => 'A burned out eatery',
            'energy_cost' => 25,
            'min_level' => 20,
            'active' => true,
            'order' => 5,
        ]);

        // WASTELAND
        $wasteland = CombatLocation::create([
            'name' => 'Wasteland',
            'description' => 'Post-apocalyptic badlands',
            'energy_cost' => 25,
            'min_level' => 25,
            'active' => true,
            'order' => 6,
        ]);

        // BUDDY'S COMPOUND
        $compound = CombatLocation::create([
            'name' => 'Buddy\'s Compound',
            'description' => 'Heavily guarded territory',
            'energy_cost' => 30,
            'min_level' => 30,
            'active' => true,
            'order' => 7,
        ]);

        // POLICE HQ
        $policeHQ = CombatLocation::create([
            'name' => 'Police HQ',
            'description' => 'High risk, high reward',
            'energy_cost' => 40,
            'min_level' => 40,
            'active' => true,
            'order' => 8,
        ]);
    }
}
