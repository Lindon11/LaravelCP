<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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
            JobsAndCompaniesSeeder::class,
            EducationCoursesSeeder::class,
            StockMarketSeeder::class,
            CasinoGamesSeeder::class,
            CombatLocationsSeeder::class,

            // ⚠️ OPTIONAL: Uncomment to create default admin user
            // DefaultAdminSeeder::class,
        ]);
    }
}
