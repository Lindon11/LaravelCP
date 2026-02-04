<?php

namespace App\Plugins\Bullets;

use App\Plugins\Plugin;

/**
 * Bullets Module
 *
 * Handles bullet purchasing for combat
 * Players can buy bullets to use in attacks
 */
class BulletsModule extends Plugin
{
    protected string $name = 'Bullets';

    public function construct(): void
    {
        $this->config = [
            'cost_per_bullet' => 100,
            'max_purchase' => 10000,
            'min_purchase' => 1,
            'bulk_discount' => [
                100 => 0.05,   // 5% off for 100+
                1000 => 0.10,  // 10% off for 1000+
                5000 => 0.15,  // 15% off for 5000+
            ],
        ];
    }

    /**
     * Calculate bullet purchase cost
     */
    public function calculateCost(int $quantity): int
    {
        $baseCost = $quantity * $this->config['cost_per_bullet'];

        // Apply bulk discounts
        foreach ($this->config['bulk_discount'] as $threshold => $discount) {
            if ($quantity >= $threshold) {
                $baseCost = $baseCost * (1 - $discount);
            }
        }

        return (int) $baseCost;
    }

    /**
     * Get player's bullet count
     */
    public function getBulletCount($user): int
    {
        return $user->bullets ?? 0;
    }

    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'cost_per_bullet' => $this->config['cost_per_bullet'],
            ];
        }

        return [
            'bullets' => $this->getBulletCount($user),
            'cost_per_bullet' => $this->config['cost_per_bullet'],
        ];
    }
}
