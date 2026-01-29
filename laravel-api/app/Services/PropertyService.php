<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    public function buyProperty(User $player, Property $property): array
    {
        if (!$property->isAvailable()) {
            throw new \Exception('This property is already owned.');
        }

        if ($player->level < $property->required_level) {
            throw new \Exception('You need to be level ' . $property->required_level . ' to buy this property.');
        }

        if ($player->cash < $property->price) {
            throw new \Exception('You do not have enough cash. Need $' . number_format($property->price));
        }

        return DB::transaction(function () use ($player, $property) {
            // Deduct cash
            $player->decrement('cash', $property->price);

            // Set ownership
            $property->update([
                'owner_id' => $player->id,
                'purchased_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'You purchased ' . $property->name . ' for $' . number_format($property->price),
            ];
        });
    }

    public function sellProperty(User $player, Property $property): array
    {
        if ($property->owner_id !== $player->id) {
            throw new \Exception('You do not own this property.');
        }

        // Sell for 70% of purchase price
        $sellPrice = $property->price * 0.7;

        return DB::transaction(function () use ($player, $property, $sellPrice) {
            // Give cash
            $player->increment('cash', $sellPrice);

            // Remove ownership
            $property->update([
                'owner_id' => null,
                'purchased_at' => null,
            ]);

            return [
                'success' => true,
                'message' => 'You sold ' . $property->name . ' for $' . number_format($sellPrice),
            ];
        });
    }

    public function collectIncome(User $player): array
    {
        $properties = Property::where('owner_id', $player->id)->get();
        
        if ($properties->isEmpty()) {
            throw new \Exception('You do not own any properties.');
        }

        $totalIncome = $properties->sum('income_per_day');

        $player->increment('cash', $totalIncome);

        return [
            'success' => true,
            'message' => 'Collected $' . number_format($totalIncome) . ' from ' . $properties->count() . ' properties',
            'amount' => $totalIncome,
        ];
    }

    public function getAvailableProperties()
    {
        return Property::whereNull('owner_id')
            ->orderBy('type')
            ->orderBy('price')
            ->get();
    }

    public function getMyProperties(User $player)
    {
        return Property::where('owner_id', $player->id)
            ->orderBy('purchased_at', 'desc')
            ->get();
    }
}
