<?php

namespace App\Services;

use App\Models\Drug;
use App\Models\User;
use App\Models\PlayerDrug;
use Illuminate\Support\Facades\DB;

class DrugService
{
    public function getDrugPrices($player)
    {
        return Drug::all()->map(function ($drug) use ($player) {
            return [
                'id' => $drug->id,
                'name' => $drug->name,
                'description' => $drug->description,
                'price' => $drug->getPriceForLocation($player->location_id),
                'bust_chance' => $drug->bust_chance,
            ];
        });
    }

    public function getPlayerDrugs($player)
    {
        return PlayerDrug::with('drug')
            ->where('user_id', $player->id)
            ->where('quantity', '>', 0)
            ->get();
    }

    public function buyDrugs($player, $drugId, $quantity)
    {
        if ($quantity <= 0) {
            throw new \Exception('Invalid quantity');
        }

        $drug = Drug::findOrFail($drugId);
        $price = $drug->getPriceForLocation($player->location_id);
        $totalCost = $price * $quantity;

        if ($player->cash < $totalCost) {
            throw new \Exception('You cannot afford ' . $quantity . ' units ($' . number_format($totalCost) . ')');
        }

        // Check for police bust
        if ($this->checkBust($drug->bust_chance)) {
            // Busted! Lose drugs and money, go to jail
            $player->decrement('cash', $totalCost);
            
            $jailTime = rand(30, 120); // 30-120 seconds
            $player->update([
                'jail_until' => now()->addSeconds($jailTime),
            ]);

            throw new \Exception('🚨 BUSTED! Police caught you! Lost $' . number_format($totalCost) . ' and sent to jail for ' . $jailTime . ' seconds!');
        }

        DB::transaction(function () use ($player, $drug, $quantity, $totalCost) {
            // Deduct cash
            $player->decrement('cash', $totalCost);

            // Add drugs
            $playerDrug = PlayerDrug::firstOrCreate(
                ['user_id' => $player->id, 'drug_id' => $drug->id],
                ['quantity' => 0]
            );

            $playerDrug->increment('quantity', $quantity);
        });

        return ['success' => true, 'drug' => $drug, 'quantity' => $quantity, 'cost' => $totalCost];
    }

    public function sellDrugs($player, $drugId, $quantity)
    {
        if ($quantity <= 0) {
            throw new \Exception('Invalid quantity');
        }

        $drug = Drug::findOrFail($drugId);
        $playerDrug = PlayerDrug::where('user_id', $player->id)
            ->where('drug_id', $drugId)
            ->first();

        if (!$playerDrug || $playerDrug->quantity < $quantity) {
            throw new \Exception('You do not have enough ' . $drug->name);
        }

        $price = $drug->getPriceForLocation($player->location_id);
        $totalEarnings = $price * $quantity;

        // Check for police bust
        if ($this->checkBust($drug->bust_chance * 0.5)) { // Lower chance when selling
            // Busted! Lose drugs, go to jail
            $playerDrug->decrement('quantity', $quantity);
            
            $jailTime = rand(30, 120);
            $player->update([
                'jail_until' => now()->addSeconds($jailTime),
            ]);

            throw new \Exception('🚨 BUSTED! Police caught you! Lost your drugs and sent to jail for ' . $jailTime . ' seconds!');
        }

        DB::transaction(function () use ($player, $playerDrug, $quantity, $totalEarnings) {
            // Remove drugs
            $playerDrug->decrement('quantity', $quantity);

            // Add cash
            $player->increment('cash', $totalEarnings);
        });

        return ['success' => true, 'drug' => $drug, 'quantity' => $quantity, 'earnings' => $totalEarnings];
    }

    protected function checkBust($chance): bool
    {
        return (rand(1, 10000) / 100) <= $chance;
    }

    public function getTotalDrugValue($player): int
    {
        $total = 0;
        $playerDrugs = $this->getPlayerDrugs($player);

        foreach ($playerDrugs as $playerDrug) {
            $price = $playerDrug->drug->getPriceForLocation($player->location_id);
            $total += $price * $playerDrug->quantity;
        }

        return $total;
    }
}
