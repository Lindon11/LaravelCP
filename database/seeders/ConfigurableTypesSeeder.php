<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Core\Models\ItemRarity;
use App\Core\Models\PropertyType;
use App\Core\Models\AnnouncementType;
use App\Core\Models\CrimeDifficulty;
use App\Core\Models\CasinoGameType;
use App\Core\Models\CompanyIndustry;
use App\Core\Models\StockSector;
use App\Core\Models\CourseSkill;
use App\Core\Models\CourseDifficulty;
use App\Core\Models\AchievementStat;
use App\Core\Models\MissionFrequency;
use App\Core\Models\MissionObjectiveType;
use App\Core\Models\BountyStatus;
use App\Core\Models\LotteryStatus;
use App\Core\Models\ItemEffectType;
use App\Core\Models\ItemModifierType;

class ConfigurableTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Item Rarities
        $rarities = [
            ['name' => 'common', 'label' => 'Common', 'color' => 'slate', 'sort_order' => 1],
            ['name' => 'uncommon', 'label' => 'Uncommon', 'color' => 'emerald', 'sort_order' => 2],
            ['name' => 'rare', 'label' => 'Rare', 'color' => 'blue', 'sort_order' => 3],
            ['name' => 'epic', 'label' => 'Epic', 'color' => 'purple', 'sort_order' => 4],
            ['name' => 'legendary', 'label' => 'Legendary', 'color' => 'amber', 'sort_order' => 5],
        ];
        foreach ($rarities as $item) {
            ItemRarity::firstOrCreate(['name' => $item['name']], $item);
        }

        // Property Types
        $propertyTypes = [
            ['name' => 'house', 'label' => 'House', 'icon' => 'HomeIcon', 'sort_order' => 1],
            ['name' => 'apartment', 'label' => 'Apartment', 'icon' => 'BuildingOfficeIcon', 'sort_order' => 2],
            ['name' => 'mansion', 'label' => 'Mansion', 'icon' => 'BuildingOffice2Icon', 'sort_order' => 3],
            ['name' => 'warehouse', 'label' => 'Warehouse', 'icon' => 'ArchiveBoxIcon', 'sort_order' => 4],
            ['name' => 'business', 'label' => 'Business', 'icon' => 'BriefcaseIcon', 'sort_order' => 5],
            ['name' => 'casino', 'label' => 'Casino', 'icon' => 'SparklesIcon', 'sort_order' => 6],
        ];
        foreach ($propertyTypes as $item) {
            PropertyType::firstOrCreate(['name' => $item['name']], $item);
        }

        // Announcement Types
        $announcementTypes = [
            ['name' => 'news', 'label' => 'News', 'color' => 'blue', 'icon' => 'NewspaperIcon', 'sort_order' => 1],
            ['name' => 'event', 'label' => 'Event', 'color' => 'purple', 'icon' => 'SparklesIcon', 'sort_order' => 2],
            ['name' => 'maintenance', 'label' => 'Maintenance', 'color' => 'amber', 'icon' => 'WrenchScrewdriverIcon', 'sort_order' => 3],
            ['name' => 'update', 'label' => 'Update', 'color' => 'emerald', 'icon' => 'RocketLaunchIcon', 'sort_order' => 4],
            ['name' => 'alert', 'label' => 'Alert', 'color' => 'red', 'icon' => 'ExclamationTriangleIcon', 'sort_order' => 5],
        ];
        foreach ($announcementTypes as $item) {
            AnnouncementType::firstOrCreate(['name' => $item['name']], $item);
        }

        // Crime Difficulties
        $crimeDifficulties = [
            ['name' => 'easy', 'label' => 'Easy', 'sort_order' => 1],
            ['name' => 'medium', 'label' => 'Medium', 'sort_order' => 2],
            ['name' => 'hard', 'label' => 'Hard', 'sort_order' => 3],
        ];
        foreach ($crimeDifficulties as $item) {
            CrimeDifficulty::firstOrCreate(['name' => $item['name']], $item);
        }

        // Casino Game Types
        $casinoGameTypes = [
            ['name' => 'slots', 'label' => 'Slots', 'icon' => 'SparklesIcon', 'sort_order' => 1],
            ['name' => 'blackjack', 'label' => 'Blackjack', 'icon' => 'CubeIcon', 'sort_order' => 2],
            ['name' => 'roulette', 'label' => 'Roulette', 'icon' => 'CubeIcon', 'sort_order' => 3],
            ['name' => 'poker', 'label' => 'Poker', 'icon' => 'CubeIcon', 'sort_order' => 4],
            ['name' => 'dice', 'label' => 'Dice', 'icon' => 'CubeIcon', 'sort_order' => 5],
            ['name' => 'lottery', 'label' => 'Lottery', 'icon' => 'StarIcon', 'sort_order' => 6],
        ];
        foreach ($casinoGameTypes as $item) {
            CasinoGameType::firstOrCreate(['name' => $item['name']], $item);
        }

        // Company Industries
        $companyIndustries = [
            ['name' => 'tech', 'label' => 'Technology', 'sort_order' => 1],
            ['name' => 'finance', 'label' => 'Finance', 'sort_order' => 2],
            ['name' => 'retail', 'label' => 'Retail', 'sort_order' => 3],
            ['name' => 'manufacturing', 'label' => 'Manufacturing', 'sort_order' => 4],
            ['name' => 'service', 'label' => 'Service', 'sort_order' => 5],
            ['name' => 'healthcare', 'label' => 'Healthcare', 'sort_order' => 6],
            ['name' => 'entertainment', 'label' => 'Entertainment', 'sort_order' => 7],
        ];
        foreach ($companyIndustries as $item) {
            CompanyIndustry::firstOrCreate(['name' => $item['name']], $item);
        }

        // Stock Sectors
        $stockSectors = [
            ['name' => 'Technology', 'label' => 'Technology', 'sort_order' => 1],
            ['name' => 'Finance', 'label' => 'Finance', 'sort_order' => 2],
            ['name' => 'Healthcare', 'label' => 'Healthcare', 'sort_order' => 3],
            ['name' => 'Energy', 'label' => 'Energy', 'sort_order' => 4],
            ['name' => 'Retail', 'label' => 'Retail', 'sort_order' => 5],
            ['name' => 'Manufacturing', 'label' => 'Manufacturing', 'sort_order' => 6],
            ['name' => 'Real Estate', 'label' => 'Real Estate', 'sort_order' => 7],
        ];
        foreach ($stockSectors as $item) {
            StockSector::firstOrCreate(['name' => $item['name']], $item);
        }

        // Course Skills
        $courseSkills = [
            ['name' => 'intelligence', 'label' => 'Intelligence', 'sort_order' => 1],
            ['name' => 'endurance', 'label' => 'Endurance', 'sort_order' => 2],
            ['name' => 'charisma', 'label' => 'Charisma', 'sort_order' => 3],
            ['name' => 'technical', 'label' => 'Technical', 'sort_order' => 4],
            ['name' => 'business', 'label' => 'Business', 'sort_order' => 5],
        ];
        foreach ($courseSkills as $item) {
            CourseSkill::firstOrCreate(['name' => $item['name']], $item);
        }

        // Course Difficulties
        $courseDifficulties = [
            ['name' => 'beginner', 'label' => 'Beginner', 'sort_order' => 1],
            ['name' => 'intermediate', 'label' => 'Intermediate', 'sort_order' => 2],
            ['name' => 'advanced', 'label' => 'Advanced', 'sort_order' => 3],
            ['name' => 'expert', 'label' => 'Expert', 'sort_order' => 4],
        ];
        foreach ($courseDifficulties as $item) {
            CourseDifficulty::firstOrCreate(['name' => $item['name']], $item);
        }

        // Achievement Stats
        $achievementStats = [
            ['name' => 'crime_count', 'label' => 'Crimes Committed', 'sort_order' => 1],
            ['name' => 'kills', 'label' => 'Players Killed', 'sort_order' => 2],
            ['name' => 'cash_earned', 'label' => 'Cash Earned', 'sort_order' => 3],
            ['name' => 'level_reached', 'label' => 'Level Reached', 'sort_order' => 4],
            ['name' => 'properties_owned', 'label' => 'Properties Owned', 'sort_order' => 5],
            ['name' => 'gang_joined', 'label' => 'Gang Joined', 'sort_order' => 6],
        ];
        foreach ($achievementStats as $item) {
            AchievementStat::firstOrCreate(['name' => $item['name']], $item);
        }

        // Mission Frequencies
        $missionFrequencies = [
            ['name' => 'one_time', 'label' => 'One Time', 'sort_order' => 1],
            ['name' => 'daily', 'label' => 'Daily', 'sort_order' => 2],
            ['name' => 'weekly', 'label' => 'Weekly', 'sort_order' => 3],
            ['name' => 'repeatable', 'label' => 'Repeatable', 'sort_order' => 4],
        ];
        foreach ($missionFrequencies as $item) {
            MissionFrequency::firstOrCreate(['name' => $item['name']], $item);
        }

        // Mission Objective Types
        $missionObjectiveTypes = [
            ['name' => 'crimes_committed', 'label' => 'Crimes Committed', 'sort_order' => 1],
            ['name' => 'players_attacked', 'label' => 'Players Attacked', 'sort_order' => 2],
            ['name' => 'cash_earned', 'label' => 'Cash Earned', 'sort_order' => 3],
            ['name' => 'experience_earned', 'label' => 'Experience Earned', 'sort_order' => 4],
            ['name' => 'gym_trains', 'label' => 'Gym Training Sessions', 'sort_order' => 5],
            ['name' => 'drugs_sold', 'label' => 'Drugs Sold', 'sort_order' => 6],
            ['name' => 'items_purchased', 'label' => 'Items Purchased', 'sort_order' => 7],
            ['name' => 'travel', 'label' => 'Travel', 'sort_order' => 8],
            ['name' => 'property_purchased', 'label' => 'Property Purchased', 'sort_order' => 9],
            ['name' => 'gang_joined', 'label' => 'Gang Joined', 'sort_order' => 10],
            ['name' => 'races_won', 'label' => 'Races Won', 'sort_order' => 11],
        ];
        foreach ($missionObjectiveTypes as $item) {
            MissionObjectiveType::firstOrCreate(['name' => $item['name']], $item);
        }

        // Bounty Statuses
        $bountyStatuses = [
            ['name' => 'active', 'label' => 'Active', 'color' => 'emerald', 'sort_order' => 1],
            ['name' => 'completed', 'label' => 'Completed', 'color' => 'blue', 'sort_order' => 2],
            ['name' => 'expired', 'label' => 'Expired', 'color' => 'slate', 'sort_order' => 3],
            ['name' => 'cancelled', 'label' => 'Cancelled', 'color' => 'red', 'sort_order' => 4],
        ];
        foreach ($bountyStatuses as $item) {
            BountyStatus::firstOrCreate(['name' => $item['name']], $item);
        }

        // Lottery Statuses
        $lotteryStatuses = [
            ['name' => 'open', 'label' => 'Open', 'color' => 'emerald', 'sort_order' => 1],
            ['name' => 'closed', 'label' => 'Closed', 'color' => 'amber', 'sort_order' => 2],
            ['name' => 'drawn', 'label' => 'Drawn', 'color' => 'blue', 'sort_order' => 3],
            ['name' => 'cancelled', 'label' => 'Cancelled', 'color' => 'red', 'sort_order' => 4],
        ];
        foreach ($lotteryStatuses as $item) {
            LotteryStatus::firstOrCreate(['name' => $item['name']], $item);
        }

        // Item Effect Types
        $itemEffectTypes = [
            ['name' => 'heal', 'label' => 'Heal (HP)', 'sort_order' => 1],
            ['name' => 'heal_percent', 'label' => 'Heal % (HP)', 'sort_order' => 2],
            ['name' => 'restore_energy', 'label' => 'Restore Energy', 'sort_order' => 3],
            ['name' => 'boost_strength', 'label' => 'Strength Boost', 'sort_order' => 4],
            ['name' => 'boost_defense', 'label' => 'Defense Boost', 'sort_order' => 5],
            ['name' => 'boost_speed', 'label' => 'Speed Boost', 'sort_order' => 6],
            ['name' => 'boost_damage', 'label' => 'Damage Boost %', 'sort_order' => 7],
            ['name' => 'reduce_cooldown', 'label' => 'Cooldown Reduction %', 'sort_order' => 8],
            ['name' => 'experience_boost', 'label' => 'XP Boost %', 'sort_order' => 9],
            ['name' => 'money_boost', 'label' => 'Money Boost %', 'sort_order' => 10],
            ['name' => 'crime_success', 'label' => 'Crime Success %', 'sort_order' => 11],
            ['name' => 'jail_reduction', 'label' => 'Jail Reduction %', 'sort_order' => 12],
            ['name' => 'revive', 'label' => 'Revive from Hospital', 'sort_order' => 13],
        ];
        foreach ($itemEffectTypes as $item) {
            ItemEffectType::firstOrCreate(['name' => $item['name']], $item);
        }

        // Item Modifier Types
        $itemModifierTypes = [
            ['name' => 'flat', 'label' => 'Flat Value', 'sort_order' => 1],
            ['name' => 'percent', 'label' => 'Percentage', 'sort_order' => 2],
        ];
        foreach ($itemModifierTypes as $item) {
            ItemModifierType::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
