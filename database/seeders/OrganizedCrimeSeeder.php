<?php

namespace Database\Seeders;

use App\Models\OrganizedCrime;
use Illuminate\Database\Seeder;

class OrganizedCrimeSeeder extends Seeder
{
    public function run(): void
    {
        $crimes = [
            ['name' => 'Rob a Bank', 'description' => 'Hit a local bank with your crew', 'success_rate' => 40, 'min_reward' => 50000, 'max_reward' => 150000, 'required_members' => 3, 'required_level' => 10, 'cooldown' => 3600],
            ['name' => 'Hijack Armored Truck', 'description' => 'Intercept an armored cash transport', 'success_rate' => 50, 'min_reward' => 100000, 'max_reward' => 250000, 'required_members' => 4, 'required_level' => 15, 'cooldown' => 3600],
            ['name' => 'Casino Heist', 'description' => 'Pull off a major casino robbery', 'success_rate' => 30, 'min_reward' => 200000, 'max_reward' => 500000, 'required_members' => 5, 'required_level' => 20, 'cooldown' => 7200],
            ['name' => 'Drug Cartel Raid', 'description' => 'Raid a rival cartel\'s warehouse', 'success_rate' => 35, 'min_reward' => 300000, 'max_reward' => 750000, 'required_members' => 6, 'required_level' => 25, 'cooldown' => 7200],
            ['name' => 'Airport Cargo Theft', 'description' => 'Steal high-value cargo from the airport', 'success_rate' => 25, 'min_reward' => 500000, 'max_reward' => 1000000, 'required_members' => 7, 'required_level' => 30, 'cooldown' => 10800],
        ];

        foreach ($crimes as $crime) {
            OrganizedCrime::firstOrCreate(
                ['name' => $crime['name']],
                $crime
            );
        }
    }
}

