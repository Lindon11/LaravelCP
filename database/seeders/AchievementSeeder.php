<?php

namespace Database\Seeders;

use App\Plugins\Achievements\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            // Crime Achievements
            [
                'name' => 'First Crime',
                'description' => 'Commit your first crime',
                'type' => 'crime_count',
                'requirement' => 1,
                'reward_cash' => 1000,
                'reward_xp' => 50,
                'icon' => '🔰',
                'sort_order' => 1
            ],
            [
                'name' => 'Petty Criminal',
                'description' => 'Commit 10 crimes',
                'type' => 'crime_count',
                'requirement' => 10,
                'reward_cash' => 5000,
                'reward_xp' => 100,
                'icon' => '🎭',
                'sort_order' => 2
            ],
            [
                'name' => 'Career Criminal',
                'description' => 'Commit 50 crimes',
                'type' => 'crime_count',
                'requirement' => 50,
                'reward_cash' => 25000,
                'reward_xp' => 500,
                'icon' => '🦹',
                'sort_order' => 3
            ],
            [
                'name' => 'Crime Lord',
                'description' => 'Commit 100 crimes',
                'type' => 'crime_count',
                'requirement' => 100,
                'reward_cash' => 100000,
                'reward_xp' => 1000,
                'icon' => '👑',
                'sort_order' => 4
            ],
            
            // Level Achievements
            [
                'name' => 'Getting Started',
                'description' => 'Reach level 5',
                'type' => 'level_reached',
                'requirement' => 5,
                'reward_cash' => 10000,
                'reward_xp' => 0,
                'icon' => '⭐',
                'sort_order' => 10
            ],
            [
                'name' => 'Rising Star',
                'description' => 'Reach level 10',
                'type' => 'level_reached',
                'requirement' => 10,
                'reward_cash' => 25000,
                'reward_xp' => 0,
                'icon' => '🌟',
                'sort_order' => 11
            ],
            [
                'name' => 'Veteran',
                'description' => 'Reach level 25',
                'type' => 'level_reached',
                'requirement' => 25,
                'reward_cash' => 100000,
                'reward_xp' => 0,
                'icon' => '💫',
                'sort_order' => 12
            ],
            [
                'name' => 'Legend',
                'description' => 'Reach level 50',
                'type' => 'level_reached',
                'requirement' => 50,
                'reward_cash' => 500000,
                'reward_xp' => 0,
                'icon' => '✨',
                'sort_order' => 13
            ],
            
            // Wealth Achievements
            [
                'name' => 'First Million',
                'description' => 'Earn $1,000,000',
                'type' => 'cash_earned',
                'requirement' => 1000000,
                'reward_cash' => 50000,
                'reward_xp' => 500,
                'icon' => '💰',
                'sort_order' => 20
            ],
            [
                'name' => 'Multi-Millionaire',
                'description' => 'Earn $10,000,000',
                'type' => 'cash_earned',
                'requirement' => 10000000,
                'reward_cash' => 250000,
                'reward_xp' => 2500,
                'icon' => '💸',
                'sort_order' => 21
            ],
            
            // Combat Achievements
            [
                'name' => 'First Blood',
                'description' => 'Win your first attack',
                'type' => 'kills',
                'requirement' => 1,
                'reward_cash' => 5000,
                'reward_xp' => 100,
                'icon' => '⚔️',
                'sort_order' => 30
            ],
            [
                'name' => 'Serial Killer',
                'description' => 'Win 10 attacks',
                'type' => 'kills',
                'requirement' => 10,
                'reward_cash' => 25000,
                'reward_xp' => 500,
                'icon' => '🗡️',
                'sort_order' => 31
            ],
            [
                'name' => 'Assassin',
                'description' => 'Win 50 attacks',
                'type' => 'kills',
                'requirement' => 50,
                'reward_cash' => 100000,
                'reward_xp' => 2000,
                'icon' => '🔪',
                'sort_order' => 32
            ],
            
            // Property Achievements
            [
                'name' => 'Homeowner',
                'description' => 'Own your first property',
                'type' => 'properties_owned',
                'requirement' => 1,
                'reward_cash' => 10000,
                'reward_xp' => 100,
                'icon' => '🏠',
                'sort_order' => 40
            ],
            [
                'name' => 'Real Estate Mogul',
                'description' => 'Own 5 properties',
                'type' => 'properties_owned',
                'requirement' => 5,
                'reward_cash' => 100000,
                'reward_xp' => 1000,
                'icon' => '🏢',
                'sort_order' => 41
            ],
            
            // Gang Achievements
            [
                'name' => 'Gang Member',
                'description' => 'Join or create a gang',
                'type' => 'gang_joined',
                'requirement' => 1,
                'reward_cash' => 25000,
                'reward_xp' => 250,
                'icon' => '🤝',
                'sort_order' => 50
            ]
        ];
        
        foreach ($achievements as $achievement) {
            Achievement::firstOrCreate(
                ['name' => $achievement['name']],
                $achievement
            );
        }
    }
}
