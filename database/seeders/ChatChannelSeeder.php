<?php

namespace Database\Seeders;

use App\Plugins\Chat\Models\ChatChannel;
use Illuminate\Database\Seeder;

class ChatChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'general',
                'description' => 'General chat for all players',
                'type' => 'public',
            ],
            [
                'name' => 'announcements',
                'description' => 'Official game announcements',
                'type' => 'announcement',
            ],
            [
                'name' => 'trading',
                'description' => 'Buy, sell, and trade items with other players',
                'type' => 'public',
            ],
            [
                'name' => 'help',
                'description' => 'Get help from other players and staff',
                'type' => 'public',
            ],
            [
                'name' => 'off-topic',
                'description' => 'Chat about anything not related to the game',
                'type' => 'public',
            ],
            [
                'name' => 'staff',
                'description' => 'Private channel for staff members',
                'type' => 'private',
            ],
        ];

        foreach ($channels as $channel) {
            ChatChannel::firstOrCreate(
                ['name' => $channel['name']],
                $channel
            );
        }
    }
}
