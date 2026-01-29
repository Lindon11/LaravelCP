<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            // Beginner Missions
            [
                'name' => 'First Steps',
                'description' => 'Welcome to the criminal underworld! Start your journey by committing your first crime.',
                'type' => 'one_time',
                'required_level' => 1,
                'objective_type' => 'crime',
                'objective_count' => 1,
                'cash_reward' => 500,
                'respect_reward' => 5,
                'experience_reward' => 10,
                'order' => 1,
            ],
            [
                'name' => 'Crime Spree',
                'description' => 'Commit 5 crimes to prove yourself in the streets.',
                'type' => 'one_time',
                'required_level' => 1,
                'objective_type' => 'crime',
                'objective_count' => 5,
                'cash_reward' => 2000,
                'respect_reward' => 15,
                'experience_reward' => 25,
                'order' => 2,
            ],
            [
                'name' => 'Get Armed',
                'description' => 'Visit the shop and purchase your first weapon.',
                'type' => 'one_time',
                'required_level' => 2,
                'objective_type' => 'purchase',
                'objective_count' => 1,
                'objective_data' => ['type' => 'weapon'],
                'cash_reward' => 1000,
                'respect_reward' => 10,
                'experience_reward' => 15,
                'order' => 3,
            ],
            [
                'name' => 'First Blood',
                'description' => 'Attack and defeat another player in combat.',
                'type' => 'one_time',
                'required_level' => 5,
                'objective_type' => 'combat',
                'objective_count' => 1,
                'cash_reward' => 3000,
                'respect_reward' => 25,
                'experience_reward' => 50,
                'order' => 4,
            ],
            [
                'name' => 'World Traveler',
                'description' => 'Travel to a different city to expand your operations.',
                'type' => 'one_time',
                'required_level' => 3,
                'objective_type' => 'travel',
                'objective_count' => 1,
                'cash_reward' => 1500,
                'respect_reward' => 10,
                'experience_reward' => 20,
                'order' => 5,
            ],

            // Daily Missions
            [
                'name' => 'Daily Grind',
                'description' => 'Commit 10 crimes today. Resets daily.',
                'type' => 'daily',
                'required_level' => 5,
                'objective_type' => 'crime',
                'objective_count' => 10,
                'cash_reward' => 5000,
                'respect_reward' => 20,
                'experience_reward' => 30,
                'order' => 100,
            ],
            [
                'name' => 'Daily Combat',
                'description' => 'Win 3 combat fights today.',
                'type' => 'daily',
                'required_level' => 10,
                'objective_type' => 'combat',
                'objective_count' => 3,
                'cash_reward' => 8000,
                'respect_reward' => 30,
                'experience_reward' => 50,
                'order' => 101,
            ],
            [
                'name' => 'Daily Hustler',
                'description' => 'Complete 3 organized crimes today.',
                'type' => 'daily',
                'required_level' => 15,
                'objective_type' => 'organized_crime',
                'objective_count' => 3,
                'cash_reward' => 10000,
                'respect_reward' => 40,
                'experience_reward' => 60,
                'order' => 102,
            ],

            // Repeatable Missions
            [
                'name' => 'Crime Wave',
                'description' => 'Commit 20 crimes. Can be repeated every 6 hours.',
                'type' => 'repeatable',
                'required_level' => 10,
                'objective_type' => 'crime',
                'objective_count' => 20,
                'cash_reward' => 8000,
                'respect_reward' => 25,
                'experience_reward' => 40,
                'cooldown_hours' => 6,
                'order' => 200,
            ],
            [
                'name' => 'Combat Master',
                'description' => 'Win 5 combat fights. Can be repeated every 12 hours.',
                'type' => 'repeatable',
                'required_level' => 15,
                'objective_type' => 'combat',
                'objective_count' => 5,
                'cash_reward' => 15000,
                'respect_reward' => 50,
                'experience_reward' => 75,
                'cooldown_hours' => 12,
                'order' => 201,
            ],

            // Story Missions
            [
                'name' => 'Join a Gang',
                'description' => 'Join or create a gang to increase your power.',
                'type' => 'one_time',
                'required_level' => 8,
                'objective_type' => 'gang_join',
                'objective_count' => 1,
                'cash_reward' => 5000,
                'respect_reward' => 30,
                'experience_reward' => 50,
                'order' => 10,
            ],
            [
                'name' => 'Property Owner',
                'description' => 'Purchase your first property to generate passive income.',
                'type' => 'one_time',
                'required_level' => 12,
                'objective_type' => 'property_purchase',
                'objective_count' => 1,
                'cash_reward' => 10000,
                'respect_reward' => 40,
                'experience_reward' => 60,
                'order' => 11,
            ],
            [
                'name' => 'Drug Dealer',
                'description' => 'Buy and sell drugs to make a profit.',
                'type' => 'one_time',
                'required_level' => 10,
                'objective_type' => 'drug_sale',
                'objective_count' => 5,
                'cash_reward' => 7500,
                'respect_reward' => 35,
                'experience_reward' => 55,
                'order' => 12,
            ],
            [
                'name' => 'Race Champion',
                'description' => 'Win your first street race.',
                'type' => 'one_time',
                'required_level' => 7,
                'objective_type' => 'race_win',
                'objective_count' => 1,
                'cash_reward' => 6000,
                'respect_reward' => 25,
                'experience_reward' => 40,
                'order' => 13,
            ],
        ];

        foreach ($missions as $mission) {
            Mission::updateOrCreate(
                ['name' => $mission['name']],
                $mission
            );
        }
    }
}
