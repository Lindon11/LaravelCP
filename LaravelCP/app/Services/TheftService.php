<?php

namespace App\Services;

use App\Models\User;
use App\Models\Car;
use App\Models\TheftType;
use App\Models\Garage;
use App\Models\TheftAttempt;
use Carbon\Carbon;

class TheftService
{
    /**
     * Check if player can attempt theft (cooldown check)
     */
    public function canAttemptTheft(User $player): bool
    {
        if (!$player->last_gta_at) {
            return true;
        }

        $cooldown = 180; // 3 minutes
        return $player->last_gta_at->addSeconds($cooldown)->isPast();
    }

    /**
     * Get remaining cooldown time in seconds
     */
    public function getRemainingCooldown(User $player): int
    {
        if (!$player->last_gta_at) {
            return 0;
        }

        $cooldown = 180;
        $availableAt = $player->last_gta_at->addSeconds($cooldown);
        
        if ($availableAt->isPast()) {
            return 0;
        }

        return now()->diffInSeconds($availableAt, false);
    }

    /**
     * Attempt to steal a car (legacy formula)
     */
    public function attemptTheft(User $player, TheftType $theftType): array
    {
        // Random rolls (legacy logic)
        $jailChance = mt_rand(1, 3); // 1/3 chance of jail on failure
        $successRoll = mt_rand(1, 100);
        $damage = mt_rand(0, $theftType->max_damage);

        // Get eligible cars for this theft type
        $eligibleCars = Car::where('value', '>=', $theftType->min_car_value)
            ->where('value', '<=', $theftType->max_car_value)
            ->get();

        // Weighted random selection (legacy logic)
        $totalWeight = $eligibleCars->sum('theft_chance');
        $randomWeight = mt_rand(1, $totalWeight);
        
        $currentWeight = 0;
        $selectedCar = null;
        
        foreach ($eligibleCars as $car) {
            $currentWeight += $car->theft_chance;
            if ($currentWeight >= $randomWeight) {
                $selectedCar = $car;
                break;
            }
        }

        // Calculate car value after damage
        $carValue = $selectedCar->value - intval($selectedCar->value / 100 * $damage);

        // Update cooldown
        $player->last_gta_at = now();
        $player->save();

        $result = [
            'success' => false,
            'caught' => false,
            'car' => $selectedCar,
            'damage' => $damage,
            'value' => $carValue,
            'message' => '',
        ];

        // Check success (legacy formula)
        if ($successRoll > $theftType->success_rate && $jailChance == 1) {
            // Failed AND caught - go to jail
            $result['caught'] = true;
            $jailTime = $theftType->id * $theftType->jail_multiplier;
            $player->jail_until = now()->addSeconds($jailTime);
            $player->save();
            
            $result['message'] = "You failed to steal a {$selectedCar->name}, you were caught and sent to jail for {$jailTime} seconds!";
            
        } else if ($successRoll > $theftType->success_rate) {
            // Failed but not caught
            $result['message'] = "You failed to steal a {$selectedCar->name}.";
            
        } else {
            // Success!
            $result['success'] = true;
            $result['message'] = "You successfully stole a {$selectedCar->name} with {$damage}% damage! It's now in your garage.";

            // Add car to garage
            Garage::create([
                'user_id' => $player->id,
                'car_id' => $selectedCar->id,
                'damage' => $damage,
                'location' => 'Chicago', // Default location
            ]);

            // Award experience
            $player->respect += 2;
            $player->save();
        }

        // Log attempt
        TheftAttempt::create([
            'user_id' => $player->id,
            'theft_type_id' => $theftType->id,
            'car_id' => $selectedCar->id,
            'success' => $result['success'],
            'caught' => $result['caught'],
            'car_value' => $result['success'] ? $carValue : 0,
            'damage' => $damage,
            'result_message' => $result['message'],
            'attempted_at' => now(),
        ]);
        
        // Track achievement progress for theft count
        $totalThefts = TheftAttempt::where('user_id', $player->id)->count();
        $achievementService = app(\App\Services\AchievementService::class);
        $earnedAchievements = $achievementService->checkProgress($player, 'theft_count', $totalThefts);
        
        // Add achievement notifications to result
        if (!empty($earnedAchievements)) {
            $result['achievements'] = $earnedAchievements;
        }

        return $result;
    }

    /**
     * Get player's garage
     */
    public function getGarage(User $player)
    {
        return Garage::where('user_id', $player->id)
            ->with('car')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'car_name' => $entry->car->name,
                    'damage' => $entry->damage,
                    'base_value' => $entry->car->value,
                    'current_value' => $entry->getCurrentValue(),
                    'location' => $entry->location,
                    'stolen_at' => $entry->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Sell a car from garage
     */
    public function sellCar(User $player, Garage $garageEntry): int
    {
        $value = $garageEntry->getCurrentValue();
        
        $player->cash += $value;
        $player->save();

        $garageEntry->delete();

        return $value;
    }
}
