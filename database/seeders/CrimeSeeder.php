<?php

namespace Database\Seeders;

use App\Plugins\Crimes\Models\Crime;
use Illuminate\Database\Seeder;

class CrimeSeeder extends Seeder
{
    public function run(): void
    {
        $crimes = [
            [
                'name' => 'Pickpocket a Pedestrian',
                'description' => 'Steal from an unsuspecting pedestrian on the street.',
                'success_rate' => 60,
                'min_cash' => 50,
                'max_cash' => 250,
                'experience_reward' => 10,
                'respect_reward' => 1,
                'cooldown_seconds' => 30,
                'energy_cost' => 10,
                'required_level' => 1,
                'difficulty' => 'easy',
                'active' => true,
            ],
            [
                'name' => 'Rob a Convenience Store',
                'description' => 'Hold up a small convenience store for quick cash.',
                'success_rate' => 50,
                'min_cash' => 200,
                'max_cash' => 800,
                'experience_reward' => 25,
                'respect_reward' => 3,
                'cooldown_seconds' => 60,
                'energy_cost' => 15,
                'required_level' => 2,
                'difficulty' => 'easy',
                'active' => true,
            ],
            [
                'name' => 'Steal a Car',
                'description' => 'Hotwire and steal a parked vehicle.',
                'success_rate' => 45,
                'min_cash' => 500,
                'max_cash' => 2000,
                'experience_reward' => 50,
                'respect_reward' => 5,
                'cooldown_seconds' => 90,
                'energy_cost' => 20,
                'required_level' => 3,
                'difficulty' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Break into a House',
                'description' => 'Burglarize a residential home while the owners are away.',
                'success_rate' => 40,
                'min_cash' => 1000,
                'max_cash' => 4000,
                'experience_reward' => 100,
                'respect_reward' => 8,
                'cooldown_seconds' => 120,
                'energy_cost' => 25,
                'required_level' => 5,
                'difficulty' => 'medium',
                'active' => true,
            ],
            [
                'name' => 'Rob a Bank',
                'description' => 'Pull off a high-stakes bank heist.',
                'success_rate' => 30,
                'min_cash' => 5000,
                'max_cash' => 15000,
                'experience_reward' => 250,
                'respect_reward' => 15,
                'cooldown_seconds' => 180,
                'energy_cost' => 35,
                'required_level' => 10,
                'difficulty' => 'hard',
                'active' => true,
            ],
            [
                'name' => 'Hijack an Armored Truck',
                'description' => 'Intercept and steal from a secure money transport.',
                'success_rate' => 25,
                'min_cash' => 10000,
                'max_cash' => 30000,
                'experience_reward' => 500,
                'respect_reward' => 25,
                'cooldown_seconds' => 300,
                'energy_cost' => 50,
                'required_level' => 15,
                'difficulty' => 'hard',
                'active' => true,
            ],
        ];

        foreach ($crimes as $crime) {
            Crime::firstOrCreate(
                ['name' => $crime['name']],
                $crime
            );
        }
    }
}
