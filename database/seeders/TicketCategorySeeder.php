<?php

namespace Database\Seeders;

use App\Plugins\Tickets\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technical Support',
                'slug' => 'technical-support',
                'description' => 'Issues with the game, bugs, glitches, and technical problems',
                'icon' => '🔧',
                'order' => 1,
            ],
            [
                'name' => 'Account Issues',
                'slug' => 'account-issues',
                'description' => 'Account access, password reset, and account-related problems',
                'icon' => '👤',
                'order' => 2,
            ],
            [
                'name' => 'Gameplay Questions',
                'slug' => 'gameplay-questions',
                'description' => 'Questions about game mechanics, features, and how to play',
                'icon' => '❓',
                'order' => 3,
            ],
            [
                'name' => 'Report Player',
                'slug' => 'report-player',
                'description' => 'Report rule violations, cheating, or inappropriate behavior',
                'icon' => '🚨',
                'order' => 4,
            ],
            [
                'name' => 'Billing & Payments',
                'slug' => 'billing-payments',
                'description' => 'Payment issues, subscriptions, and refund requests',
                'icon' => '💳',
                'order' => 5,
            ],
            [
                'name' => 'Suggestions',
                'slug' => 'suggestions',
                'description' => 'Feature requests and suggestions for game improvements',
                'icon' => '💡',
                'order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            TicketCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
