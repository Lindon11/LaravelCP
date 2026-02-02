<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockMarketSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            [
                'symbol' => 'TECH',
                'name' => 'TechCorp Industries',
                'sector' => 'Technology',
                'description' => 'Leading technology and software company',
                'current_price' => 100.00,
                'shares_available' => 1000000,
                'shares_traded' => 0,
                'market_cap' => 100000000.00,
                'volatility' => 15.5,
                'is_active' => true,
            ],
            [
                'symbol' => 'MED',
                'name' => 'MediCare Solutions',
                'sector' => 'Healthcare',
                'description' => 'Healthcare and pharmaceutical company',
                'current_price' => 75.50,
                'shares_available' => 800000,
                'shares_traded' => 0,
                'market_cap' => 60400000.00,
                'volatility' => 12.0,
                'is_active' => true,
            ],
            [
                'symbol' => 'FIN',
                'name' => 'Financial Group',
                'sector' => 'Finance',
                'description' => 'Banking and investment services',
                'current_price' => 150.00,
                'shares_available' => 500000,
                'shares_traded' => 0,
                'market_cap' => 75000000.00,
                'volatility' => 10.5,
                'is_active' => true,
            ],
            [
                'symbol' => 'ENR',
                'name' => 'Energy Corp',
                'sector' => 'Energy',
                'description' => 'Renewable and traditional energy provider',
                'current_price' => 85.25,
                'shares_available' => 1200000,
                'shares_traded' => 0,
                'market_cap' => 102300000.00,
                'volatility' => 18.0,
                'is_active' => true,
            ],
            [
                'symbol' => 'RET',
                'name' => 'Retail Giants',
                'sector' => 'Retail',
                'description' => 'Leading retail and e-commerce platform',
                'current_price' => 50.00,
                'shares_available' => 2000000,
                'shares_traded' => 0,
                'market_cap' => 100000000.00,
                'volatility' => 20.0,
                'is_active' => true,
            ],
            [
                'symbol' => 'AUTO',
                'name' => 'Auto Manufacturers',
                'sector' => 'Automotive',
                'description' => 'Electric and traditional vehicle manufacturing',
                'current_price' => 120.75,
                'shares_available' => 600000,
                'shares_traded' => 0,
                'market_cap' => 72450000.00,
                'volatility' => 22.5,
                'is_active' => true,
            ],
            [
                'symbol' => 'PROP',
                'name' => 'Property Holdings',
                'sector' => 'Real Estate',
                'description' => 'Commercial and residential real estate',
                'current_price' => 95.00,
                'shares_available' => 700000,
                'shares_traded' => 0,
                'market_cap' => 66500000.00,
                'volatility' => 8.5,
                'is_active' => true,
            ],
            [
                'symbol' => 'FOOD',
                'name' => 'Food Industries',
                'sector' => 'Consumer Goods',
                'description' => 'Food production and distribution',
                'current_price' => 45.50,
                'shares_available' => 1500000,
                'shares_traded' => 0,
                'market_cap' => 68250000.00,
                'volatility' => 9.0,
                'is_active' => true,
            ],
        ];

        foreach ($stocks as $stock) {
            $stockId = DB::table('stocks')->insertGetId(array_merge($stock, [
                'day_open' => $stock['current_price'],
                'day_high' => $stock['current_price'],
                'day_low' => $stock['current_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Create initial price history
            $this->createInitialPriceHistory($stockId, $stock['current_price'], $stock['volatility']);
        }
    }

    private function createInitialPriceHistory(int $stockId, float $basePrice, float $volatility): void
    {
        // Create 30 days of historical data
        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            // Generate random price variation based on volatility
            $change = (rand(-100, 100) / 100) * ($volatility / 100) * $basePrice;
            $price = max(1, $basePrice + $change);
            
            $open = $price * (1 + (rand(-50, 50) / 1000));
            $close = $price * (1 + (rand(-50, 50) / 1000));
            $high = max($open, $close) * (1 + (rand(0, 30) / 1000));
            $low = min($open, $close) * (1 - (rand(0, 30) / 1000));
            
            DB::table('stock_price_history')->insert([
                'stock_id' => $stockId,
                'price' => round($price, 2),
                'open_price' => round($open, 2),
                'close_price' => round($close, 2),
                'high_price' => round($high, 2),
                'low_price' => round($low, 2),
                'volume' => rand(1000, 50000),
                'recorded_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
