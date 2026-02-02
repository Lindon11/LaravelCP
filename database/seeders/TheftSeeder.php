<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TheftSeeder extends Seeder
{
    public function run(): void
    {
        // Cars available to steal
        $cars = [
            ['name' => 'Honda Civic', 'description' => 'Common sedan', 'value' => 5000, 'theft_chance' => 150, 'required_level' => 1],
            ['name' => 'Toyota Camry', 'description' => 'Reliable family car', 'value' => 8000, 'theft_chance' => 140, 'required_level' => 1],
            ['name' => 'Ford Mustang', 'description' => 'American muscle', 'value' => 25000, 'theft_chance' => 100, 'required_level' => 5],
            ['name' => 'BMW 3 Series', 'description' => 'Luxury sedan', 'value' => 40000, 'theft_chance' => 80, 'required_level' => 8],
            ['name' => 'Mercedes S-Class', 'description' => 'Executive luxury', 'value' => 80000, 'theft_chance' => 60, 'required_level' => 12],
            ['name' => 'Porsche 911', 'description' => 'Sports car', 'value' => 120000, 'theft_chance' => 40, 'required_level' => 15],
            ['name' => 'Ferrari F8', 'description' => 'Exotic supercar', 'value' => 280000, 'theft_chance' => 20, 'required_level' => 20],
            ['name' => 'Lamborghini Aventador', 'description' => 'Ultimate supercar', 'value' => 500000, 'theft_chance' => 10, 'required_level' => 25],
        ];

        foreach ($cars as $car) {
            DB::table('cars')->insert($car + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Theft difficulty types (matching legacy T_id logic)
        $theftTypes = [
            [
                'name' => 'Easy Target',
                'description' => 'Steal from unlocked cars in parking lots. Low risk, low reward.',
                'success_rate' => 75,
                'jail_multiplier' => 35,
                'min_car_value' => 5000,
                'max_car_value' => 15000,
                'max_damage' => 20,
                'cooldown' => 180,
                'required_level' => 1,
            ],
            [
                'name' => 'Street Job',
                'description' => 'Hot-wire cars parked on the street. Moderate risk and reward.',
                'success_rate' => 55,
                'jail_multiplier' => 35,
                'min_car_value' => 15000,
                'max_car_value' => 50000,
                'max_damage' => 35,
                'cooldown' => 180,
                'required_level' => 5,
            ],
            [
                'name' => 'Risky Job',
                'description' => 'Break into secured parking structures. High risk, good reward.',
                'success_rate' => 40,
                'jail_multiplier' => 35,
                'min_car_value' => 40000,
                'max_car_value' => 150000,
                'max_damage' => 50,
                'cooldown' => 180,
                'required_level' => 10,
            ],
            [
                'name' => 'High Stakes',
                'description' => 'Target luxury dealerships and private collections. Very high risk, massive reward.',
                'success_rate' => 25,
                'jail_multiplier' => 35,
                'min_car_value' => 120000,
                'max_car_value' => 500000,
                'max_damage' => 60,
                'cooldown' => 180,
                'required_level' => 15,
            ],
        ];

        foreach ($theftTypes as $type) {
            DB::table('theft_types')->insert($type + ['created_at' => now(), 'updated_at' => now()]);
        }
    }
}
