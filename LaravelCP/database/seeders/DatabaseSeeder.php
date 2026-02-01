<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user if doesn't exist
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Seed all game content
        $this->call([
            RolePermissionSeeder::class,
            SettingsTableSeeder::class,
            ModuleSeeder::class,
            LocationSeeder::class,
            RanksTableSeeder::class,
            CrimeSeeder::class,
            TheftSeeder::class,
            DrugSeeder::class,
            ItemSeeder::class,
            PropertySeeder::class,
            OrganizedCrimeSeeder::class,
            MissionSeeder::class,
            AchievementSeeder::class,
            ChatChannelSeeder::class,
            ForumSeeder::class,
            TicketCategorySeeder::class,
        ]);
    }
}
