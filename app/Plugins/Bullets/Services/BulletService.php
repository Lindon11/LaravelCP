<?php

namespace App\Plugins\Bullets\Services;

use App\Core\Models\User;

class BulletService
{
    /**
     * Cost per bullet
     */
    const COST_PER_BULLET = 50;

    /**
     * Calculate cost for specific number of bullets
     */
    public function calculateCost(int $quantity): int
    {
        return $quantity * self::COST_PER_BULLET;
    }

    /**
     * Buy bullets
     */
    public function buyBullets(User $player, int $quantity): array
    {
        if ($quantity <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid quantity!',
            ];
        }

        $cost = $this->calculateCost($quantity);

        if ($player->cash < $cost) {
            return [
                'success' => false,
                'message' => 'You do not have enough cash!',
            ];
        }

        $player->bullets += $quantity;
        $player->cash -= $cost;
        $player->save();

        return [
            'success' => true,
            'quantity' => $quantity,
            'cost' => $cost,
            'message' => "You bought {$quantity} bullets for $" . number_format($cost) . "!",
        ];
    }
}
