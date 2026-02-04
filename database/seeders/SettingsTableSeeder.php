<?php

namespace Database\Seeders;

use App\Core\Services\SettingService;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = app(SettingService::class);

        // General Settings
        $settings->set('game_name', 'OpenPBBG', 'The name of the game', 'general');
        $settings->set('game_tagline', 'Build Your Empire', 'Game tagline/slogan', 'general');
        $settings->set('registration_enabled', true, 'Allow new user registrations', 'general');
        $settings->set('maintenance_mode', false, 'Put game in maintenance mode', 'general');
        
        // Gameplay Settings
        $settings->set('starting_cash', 10000, 'Starting cash for new players', 'game');
        $settings->set('starting_bullets', 50, 'Starting bullets for new players', 'game');
        $settings->set('starting_energy', 100, 'Starting energy for new players', 'game');
        $settings->set('max_energy', 100, 'Maximum energy for players', 'game');
        $settings->set('energy_refill_rate', 5, 'Energy points refilled per minute', 'game');
        $settings->set('respect_for_level', 100, 'Respect needed per level', 'game');
        
        // Crime Settings
        $settings->set('global_crime_cooldown', 30, 'Global cooldown between crimes (seconds)', 'game');
        $settings->set('jail_time_multiplier', 15, 'Jail time multiplier (seconds per crime level)', 'game');
        
        // Economy Settings
        $settings->set('bank_interest_rate', 2.5, 'Bank interest rate percentage', 'economy');
        $settings->set('max_bank_deposit', 1000000, 'Maximum bank deposit amount', 'economy');
        
        // Combat Settings
        $settings->set('hospital_time_base', 60, 'Base hospital time in seconds', 'combat');
        $settings->set('bullet_cost', 100, 'Cost per bullet', 'combat');
        
        // Gang Settings
        $settings->set('gang_creation_cost', 50000, 'Cost to create a gang', 'gangs');
        $settings->set('max_gang_members', 20, 'Maximum gang members', 'gangs');
        
        // Email Settings
        $settings->set('email_notifications', true, 'Enable email notifications', 'email');
        $settings->set('admin_email', 'admin@gangsterlegends.com', 'Admin email address', 'email');
    }
}
