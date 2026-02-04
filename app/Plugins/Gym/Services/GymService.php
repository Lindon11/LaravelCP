<?php

namespace App\Plugins\Gym\Services;

use App\Core\Models\User;

class GymService
{
    const TRAINING_COSTS = [
        'strength' => 100,
        'defense' => 100,
        'speed' => 150,
        'stamina' => 200,
    ];

    const MAX_TRAINING_PER_DAY = 50;

    public function train(User $player, string $attribute, int $times = 1): array
    {
        if (!isset(self::TRAINING_COSTS[$attribute])) {
            throw new \Exception('Invalid training attribute');
        }

        if ($times < 1 || $times > 100) {
            throw new \Exception('You can train 1-100 times at once');
        }

        $totalCost = self::TRAINING_COSTS[$attribute] * $times;

        if ($player->cash < $totalCost) {
            throw new \Exception('Not enough cash. Need $' . number_format($totalCost));
        }

        // Deduct cash
        $player->decrement('cash', $totalCost);

        // Award stats (stored in respect for now - can expand player table later)
        $statIncrease = $times;
        $player->increment('respect', $statIncrease);

        return [
            'success' => true,
            'message' => 'You trained ' . $attribute . ' ' . $times . ' times! +' . $statIncrease . ' stats',
            'cost' => $totalCost,
            'gained' => $statIncrease,
        ];
    }

    public function getTrainingInfo()
    {
        return [
            'costs' => self::TRAINING_COSTS,
            'max_per_session' => self::MAX_TRAINING_PER_DAY,
        ];
    }
}
