<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $drugs = [
            [
                'name' => 'Weed',
                'description' => 'Low risk, low reward. Good for beginners.',
                'base_price' => 500,
                'min_price' => 300,
                'max_price' => 800,
                'bust_chance' => 5.00,
            ],
            [
                'name' => 'Cocaine',
                'description' => 'Medium risk, medium reward.',
                'base_price' => 2000,
                'min_price' => 1200,
                'max_price' => 3500,
                'bust_chance' => 15.00,
            ],
            [
                'name' => 'Heroin',
                'description' => 'High risk, high reward.',
                'base_price' => 5000,
                'min_price' => 3000,
                'max_price' => 8000,
                'bust_chance' => 25.00,
            ],
            [
                'name' => 'Meth',
                'description' => 'Very high risk, very high reward.',
                'base_price' => 8000,
                'min_price' => 5000,
                'max_price' => 12000,
                'bust_chance' => 30.00,
            ],
            [
                'name' => 'Ecstasy',
                'description' => 'Party drug with moderate risk.',
                'base_price' => 1500,
                'min_price' => 900,
                'max_price' => 2500,
                'bust_chance' => 12.00,
            ],
        ];

        foreach ($drugs as $drug) {
            Drug::updateOrCreate(
                ['name' => $drug['name']],
                $drug
            );
        }
    }
}
