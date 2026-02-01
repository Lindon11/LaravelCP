<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = [
            // Houses
            ['name' => 'Small Apartment', 'type' => 'house', 'description' => 'A cramped studio apartment', 'price' => 50000, 'income_per_day' => 100, 'required_level' => 1],
            ['name' => 'Townhouse', 'type' => 'house', 'description' => 'A modest 2-bedroom townhouse', 'price' => 150000, 'income_per_day' => 350, 'required_level' => 5],
            ['name' => 'Luxury Condo', 'type' => 'house', 'description' => 'High-rise condo with a view', 'price' => 500000, 'income_per_day' => 1200, 'required_level' => 10],
            ['name' => 'Mansion', 'type' => 'house', 'description' => 'Sprawling estate with pool', 'price' => 2000000, 'income_per_day' => 5000, 'required_level' => 20],
            
            // Businesses
            ['name' => 'Corner Store', 'type' => 'business', 'description' => 'Small convenience store', 'price' => 100000, 'income_per_day' => 250, 'required_level' => 3],
            ['name' => 'Bar & Grill', 'type' => 'business', 'description' => 'Popular local bar', 'price' => 300000, 'income_per_day' => 800, 'required_level' => 7],
            ['name' => 'Nightclub', 'type' => 'business', 'description' => 'High-end nightclub', 'price' => 800000, 'income_per_day' => 2500, 'required_level' => 12],
            ['name' => 'Casino', 'type' => 'business', 'description' => 'Exclusive casino', 'price' => 5000000, 'income_per_day' => 15000, 'required_level' => 25],
            
            // Warehouses
            ['name' => 'Storage Unit', 'type' => 'warehouse', 'description' => 'Small storage facility', 'price' => 75000, 'income_per_day' => 200, 'required_level' => 2],
            ['name' => 'Warehouse', 'type' => 'warehouse', 'description' => 'Mid-size warehouse', 'price' => 400000, 'income_per_day' => 1000, 'required_level' => 8],
            ['name' => 'Distribution Center', 'type' => 'warehouse', 'description' => 'Large distribution center', 'price' => 1500000, 'income_per_day' => 4000, 'required_level' => 15],
            ['name' => 'Industrial Complex', 'type' => 'warehouse', 'description' => 'Massive industrial facility', 'price' => 10000000, 'income_per_day' => 30000, 'required_level' => 30],
        ];

        foreach ($properties as $property) {
            Property::firstOrCreate(
                ['name' => $property['name']],
                $property
            );
        }
    }
}
