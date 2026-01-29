<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'Thug', 'required_level' => 1, 'attack_bonus' => 0, 'defense_bonus' => 0],
            ['name' => 'Hustler', 'required_level' => 5, 'attack_bonus' => 5, 'defense_bonus' => 5],
            ['name' => 'Gangster', 'required_level' => 10, 'attack_bonus' => 10, 'defense_bonus' => 10],
            ['name' => 'Enforcer', 'required_level' => 15, 'attack_bonus' => 15, 'defense_bonus' => 15],
            ['name' => 'Capo', 'required_level' => 20, 'attack_bonus' => 25, 'defense_bonus' => 25],
            ['name' => 'Underboss', 'required_level' => 30, 'attack_bonus' => 40, 'defense_bonus' => 40],
            ['name' => 'Consigliere', 'required_level' => 40, 'attack_bonus' => 60, 'defense_bonus' => 60],
            ['name' => 'Boss', 'required_level' => 50, 'attack_bonus' => 80, 'defense_bonus' => 80],
            ['name' => 'Don', 'required_level' => 75, 'attack_bonus' => 120, 'defense_bonus' => 120],
            ['name' => 'Godfather', 'required_level' => 100, 'attack_bonus' => 200, 'defense_bonus' => 200],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['name' => $rank['name']],
                $rank
            );
        }
    }
}
