<?php

namespace Database\Seeders;

use App\Plugins\Forum\Models\ForumCategory;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'General Discussion', 'description' => 'Talk about anything related to the game', 'order' => 1],
            ['name' => 'Gang Recruitment', 'description' => 'Looking for a gang or recruiting members?', 'order' => 2],
            ['name' => 'Game Help', 'description' => 'Need help? Ask here', 'order' => 3],
            ['name' => 'Trading Post', 'description' => 'Buy, sell, and trade items', 'order' => 4],
            ['name' => 'Off Topic', 'description' => 'Discuss anything not game related', 'order' => 5],
        ];

        foreach ($categories as $category) {
            ForumCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}

