<?php

namespace Database\Seeders;

use App\Core\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Weapons
            [
                'name' => 'Baseball Bat',
                'type' => 'weapon',
                'description' => 'A wooden baseball bat. Good for close combat.',
                'price' => 500,
                'sell_price' => 250,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['damage' => 15],
                'requirements' => ['level' => 1],
                'rarity' => 'common',
            ],
            [
                'name' => 'Pistol',
                'type' => 'weapon',
                'description' => 'A standard 9mm pistol.',
                'price' => 2500,
                'sell_price' => 1250,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['damage' => 30],
                'requirements' => ['level' => 5],
                'rarity' => 'uncommon',
            ],
            [
                'name' => 'Shotgun',
                'type' => 'weapon',
                'description' => 'A pump-action shotgun. Devastating at close range.',
                'price' => 8000,
                'sell_price' => 4000,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['damage' => 60],
                'requirements' => ['level' => 15],
                'rarity' => 'rare',
            ],
            [
                'name' => 'AK-47',
                'type' => 'weapon',
                'description' => 'Legendary assault rifle. High damage and rate of fire.',
                'price' => 25000,
                'sell_price' => 12500,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['damage' => 100],
                'requirements' => ['level' => 30],
                'rarity' => 'epic',
            ],
            
            // Armor
            [
                'name' => 'Leather Jacket',
                'type' => 'armor',
                'description' => 'A tough leather jacket. Provides basic protection.',
                'price' => 1000,
                'sell_price' => 500,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['defense' => 10],
                'requirements' => ['level' => 1],
                'rarity' => 'common',
            ],
            [
                'name' => 'Bulletproof Vest',
                'type' => 'armor',
                'description' => 'Military-grade body armor.',
                'price' => 5000,
                'sell_price' => 2500,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['defense' => 30],
                'requirements' => ['level' => 10],
                'rarity' => 'rare',
            ],
            [
                'name' => 'Tactical Armor',
                'type' => 'armor',
                'description' => 'Advanced tactical armor with ceramic plates.',
                'price' => 15000,
                'sell_price' => 7500,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['defense' => 50],
                'requirements' => ['level' => 25],
                'rarity' => 'epic',
            ],
            
            // Vehicles
            [
                'name' => 'Motorcycle',
                'type' => 'vehicle',
                'description' => 'Fast and agile. Perfect for quick getaways.',
                'price' => 10000,
                'sell_price' => 5000,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['speed' => 20],
                'requirements' => ['level' => 5],
                'rarity' => 'uncommon',
            ],
            [
                'name' => 'Sports Car',
                'type' => 'vehicle',
                'description' => 'High-performance sports car.',
                'price' => 50000,
                'sell_price' => 25000,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['speed' => 50],
                'requirements' => ['level' => 20],
                'rarity' => 'rare',
            ],
            [
                'name' => 'Armored SUV',
                'type' => 'vehicle',
                'description' => 'Bulletproof and fast. The ultimate gangster vehicle.',
                'price' => 200000,
                'sell_price' => 100000,
                'tradeable' => true,
                'stackable' => false,
                'stats' => ['speed' => 40, 'defense' => 30],
                'requirements' => ['level' => 40],
                'rarity' => 'legendary',
            ],
            
            // Consumables
            [
                'name' => 'First Aid Kit',
                'type' => 'consumable',
                'description' => 'Restores 50 health.',
                'price' => 500,
                'sell_price' => 100,
                'tradeable' => true,
                'stackable' => true,
                'max_stack' => 10,
                'stats' => ['health' => 50],
                'requirements' => ['level' => 1],
                'rarity' => 'common',
            ],
            [
                'name' => 'Energy Drink',
                'type' => 'consumable',
                'description' => 'Restores 25 health instantly.',
                'price' => 200,
                'sell_price' => 50,
                'tradeable' => true,
                'stackable' => true,
                'max_stack' => 20,
                'stats' => ['health' => 25],
                'requirements' => ['level' => 1],
                'rarity' => 'common',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
