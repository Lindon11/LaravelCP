<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        // V2-style rank progression based on experience
        $ranks = [
            ['name' => 'Thug', 'required_exp' => 0, 'max_health' => 100, 'cash_reward' => 0, 'bullet_reward' => 0, 'user_limit' => 0],
            ['name' => 'Hustler', 'required_exp' => 100, 'max_health' => 150, 'cash_reward' => 5000, 'bullet_reward' => 50, 'user_limit' => 0],
            ['name' => 'Gangster', 'required_exp' => 500, 'max_health' => 200, 'cash_reward' => 25000, 'bullet_reward' => 100, 'user_limit' => 0],
            ['name' => 'Enforcer', 'required_exp' => 1500, 'max_health' => 300, 'cash_reward' => 75000, 'bullet_reward' => 250, 'user_limit' => 0],
            ['name' => 'Capo', 'required_exp' => 3500, 'max_health' => 450, 'cash_reward' => 150000, 'bullet_reward' => 500, 'user_limit' => 0],
            ['name' => 'Underboss', 'required_exp' => 7500, 'max_health' => 650, 'cash_reward' => 350000, 'bullet_reward' => 1000, 'user_limit' => 0],
            ['name' => 'Consigliere', 'required_exp' => 15000, 'max_health' => 900, 'cash_reward' => 750000, 'bullet_reward' => 2000, 'user_limit' => 0],
            ['name' => 'Boss', 'required_exp' => 30000, 'max_health' => 1250, 'cash_reward' => 1500000, 'bullet_reward' => 4000, 'user_limit' => 0],
            ['name' => 'Don', 'required_exp' => 60000, 'max_health' => 1750, 'cash_reward' => 3000000, 'bullet_reward' => 8000, 'user_limit' => 0],
            ['name' => 'Godfather', 'required_exp' => 125000, 'max_health' => 2500, 'cash_reward' => 6000000, 'bullet_reward' => 15000, 'user_limit' => 0],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['name' => $rank['name']],
                $rank
            );
        }
    }
}
