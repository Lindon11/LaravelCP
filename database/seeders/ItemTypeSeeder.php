<?php

namespace Database\Seeders;

use App\Core\Models\ItemType;
use Illuminate\Database\Seeder;

class ItemTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'weapon',      'label' => 'Weapons',       'icon' => 'SparklesIcon',     'sort_order' => 1],
            ['name' => 'armor',       'label' => 'Armor',         'icon' => 'ShieldCheckIcon',  'sort_order' => 2],
            ['name' => 'consumable',  'label' => 'Consumables',   'icon' => 'BeakerIcon',       'sort_order' => 3],
            ['name' => 'collectible', 'label' => 'Collectibles',  'icon' => 'TrophyIcon',       'sort_order' => 4],
            ['name' => 'misc',        'label' => 'Miscellaneous', 'icon' => 'ArchiveBoxIcon',   'sort_order' => 5],
        ];

        foreach ($types as $type) {
            ItemType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
