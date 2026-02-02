<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Detroit', 'description' => 'The Motor City. Where it all begins.', 'travel_cost' => 0, 'required_level' => 1],
            ['name' => 'Chicago', 'description' => 'The Windy City. Home of organized crime.', 'travel_cost' => 1000, 'required_level' => 5],
            ['name' => 'New York', 'description' => 'The Big Apple. Where fortunes are made.', 'travel_cost' => 2500, 'required_level' => 10],
            ['name' => 'Las Vegas', 'description' => 'Sin City. Gamble everything or lose it all.', 'travel_cost' => 5000, 'required_level' => 15],
            ['name' => 'Miami', 'description' => 'Tropical paradise with a dark underbelly.', 'travel_cost' => 7500, 'required_level' => 20],
            ['name' => 'Los Angeles', 'description' => 'City of Angels. City of crime.', 'travel_cost' => 10000, 'required_level' => 25],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['name' => $location['name']],
                $location
            );
        }
    }
}

