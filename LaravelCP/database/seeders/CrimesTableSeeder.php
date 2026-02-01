<?php

namespace Database\Seeders;

use App\Models\Crime;
use Illuminate\Database\Seeder;

class CrimesTableSeeder extends Seeder
{
    public function run(): void
    {
        $crimes = [
            ['name' => 'Steal Candy', 'description' => 'Take candy from a baby', 'success_rate' => 95, 'min_cash' => 10, 'max_cash' => 50, 'respect_reward' => 1, 'cooldown_seconds' => 10, 'energy_cost' => 5, 'experience_reward' => 5, 'required_level' => 1, 'difficulty' => 'easy', 'active' => true],
            ['name' => 'Shoplift', 'description' => 'Steal items from corner store', 'success_rate' => 85, 'min_cash' => 50, 'max_cash' => 150, 'respect_reward' => 2, 'cooldown_seconds' => 15, 'energy_cost' => 8, 'experience_reward' => 8, 'required_level' => 1, 'difficulty' => 'easy', 'active' => true],
            ['name' => 'Pickpocket', 'description' => 'Steal a wallet from a victim', 'success_rate' => 75, 'min_cash' => 100, 'max_cash' => 300, 'respect_reward' => 3, 'cooldown_seconds' => 20, 'energy_cost' => 10, 'experience_reward' => 12, 'required_level' => 1, 'difficulty' => 'easy', 'active' => true],
            ['name' => 'Steal a Car', 'description' => 'Hot-wire a parked car', 'success_rate' => 65, 'min_cash' => 500, 'max_cash' => 1500, 'respect_reward' => 5, 'cooldown_seconds' => 30, 'energy_cost' => 15, 'experience_reward' => 20, 'required_level' => 2, 'difficulty' => 'medium', 'active' => true],
            ['name' => 'Break into House', 'description' => 'Break and enter residential home', 'success_rate' => 60, 'min_cash' => 1000, 'max_cash' => 3000, 'respect_reward' => 7, 'cooldown_seconds' => 40, 'energy_cost' => 20, 'experience_reward' => 30, 'required_level' => 3, 'difficulty' => 'medium', 'active' => true],
            ['name' => 'Mug Someone', 'description' => 'Rob someone at gunpoint', 'success_rate' => 55, 'min_cash' => 2000, 'max_cash' => 5000, 'respect_reward' => 10, 'cooldown_seconds' => 50, 'energy_cost' => 25, 'experience_reward' => 40, 'required_level' => 4, 'difficulty' => 'medium', 'active' => true],
            ['name' => 'Rob a Store', 'description' => 'Hold up a convenience store', 'success_rate' => 50, 'min_cash' => 3000, 'max_cash' => 8000, 'respect_reward' => 15, 'cooldown_seconds' => 60, 'energy_cost' => 30, 'experience_reward' => 55, 'required_level' => 5, 'difficulty' => 'medium', 'active' => true],
            ['name' => 'Rob a Bank', 'description' => 'Bank heist - high risk high reward', 'success_rate' => 40, 'min_cash' => 10000, 'max_cash' => 25000, 'respect_reward' => 25, 'cooldown_seconds' => 90, 'energy_cost' => 40, 'experience_reward' => 80, 'required_level' => 6, 'difficulty' => 'hard', 'active' => true],
            ['name' => 'Hijack Armored Truck', 'description' => 'Take down money transport', 'success_rate' => 35, 'min_cash' => 15000, 'max_cash' => 40000, 'respect_reward' => 35, 'cooldown_seconds' => 120, 'energy_cost' => 50, 'experience_reward' => 120, 'required_level' => 8, 'difficulty' => 'hard', 'active' => true],
            ['name' => 'Steal Military Weapons', 'description' => 'Raid military base for weapons', 'success_rate' => 30, 'min_cash' => 25000, 'max_cash' => 60000, 'respect_reward' => 50, 'cooldown_seconds' => 150, 'energy_cost' => 60, 'experience_reward' => 180, 'required_level' => 10, 'difficulty' => 'hard', 'active' => true],
            ['name' => 'Steal a Yacht', 'description' => 'Take luxury yacht from harbor', 'success_rate' => 25, 'min_cash' => 50000, 'max_cash' => 100000, 'respect_reward' => 75, 'cooldown_seconds' => 180, 'energy_cost' => 75, 'experience_reward' => 250, 'required_level' => 15, 'difficulty' => 'expert', 'active' => true],
            ['name' => 'Rob Casino', 'description' => 'Ocean\'s Eleven style heist', 'success_rate' => 20, 'min_cash' => 75000, 'max_cash' => 150000, 'respect_reward' => 100, 'cooldown_seconds' => 240, 'energy_cost' => 100, 'experience_reward' => 350, 'required_level' => 20, 'difficulty' => 'expert', 'active' => true],
        ];

        foreach ($crimes as $crime) {
            Crime::firstOrCreate(
                ['name' => $crime['name']],
                $crime
            );
        }
    }
}
