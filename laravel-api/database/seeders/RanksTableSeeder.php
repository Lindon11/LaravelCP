<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RanksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['name' => 'Thug', 'required_level' => 1, 'attack_bonus' => 0, 'defense_bonus' => 0],
            ['name' => 'Street Dealer', 'required_level' => 5, 'attack_bonus' => 5, 'defense_bonus' => 5],
            ['name' => 'Hustler', 'required_level' => 10, 'attack_bonus' => 10, 'defense_bonus' => 10],
            ['name' => 'Enforcer', 'required_level' => 15, 'attack_bonus' => 20, 'defense_bonus' => 15],
            ['name' => 'Hitman', 'required_level' => 20, 'attack_bonus' => 30, 'defense_bonus' => 20],
            ['name' => 'Soldier', 'required_level' => 25, 'attack_bonus' => 40, 'defense_bonus' => 30],
            ['name' => 'Capo', 'required_level' => 30, 'attack_bonus' => 55, 'defense_bonus' => 40],
            ['name' => 'Underboss', 'required_level' => 40, 'attack_bonus' => 75, 'defense_bonus' => 55],
            ['name' => 'Consigliere', 'required_level' => 50, 'attack_bonus' => 100, 'defense_bonus' => 75],
            ['name' => 'Boss', 'required_level' => 60, 'attack_bonus' => 130, 'defense_bonus' => 100],
            ['name' => 'Don', 'required_level' => 75, 'attack_bonus' => 170, 'defense_bonus' => 130],
            ['name' => 'Kingpin', 'required_level' => 90, 'attack_bonus' => 220, 'defense_bonus' => 170],
            ['name' => 'Godfather', 'required_level' => 100, 'attack_bonus' => 300, 'defense_bonus' => 250],
        ];

        foreach ($ranks as $rank) {
            Rank::create($rank);
        }
    }
}
