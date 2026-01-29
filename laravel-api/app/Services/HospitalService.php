<?php

namespace App\Services;

use App\Models\User;

class HospitalService
{
    /**
     * Cost per health point ($100 per HP)
     */
    const COST_PER_HP = 100;

    /**
     * Calculate cost to heal a specific amount
     */
    public function calculateHealCost(int $healthPoints): int
    {
        return $healthPoints * self::COST_PER_HP;
    }

    /**
     * Calculate cost to heal to full health
     */
    public function calculateFullHealCost(User $player): int
    {
        $healthNeeded = $player->max_health - $player->health;
        return $this->calculateHealCost($healthNeeded);
    }

    /**
     * Heal player by specific amount
     */
    public function heal(User $player, int $healthPoints): array
    {
        $cost = $this->calculateHealCost($healthPoints);

        if ($player->cash < $cost) {
            return [
                'success' => false,
                'message' => 'You do not have enough cash to afford this treatment!',
            ];
        }

        if ($player->health >= $player->max_health) {
            return [
                'success' => false,
                'message' => 'You are already at full health!',
            ];
        }

        // Cap heal amount to not exceed max health
        $actualHeal = min($healthPoints, $player->max_health - $player->health);
        $actualCost = $this->calculateHealCost($actualHeal);

        $player->health += $actualHeal;
        $player->cash -= $actualCost;
        $player->save();

        return [
            'success' => true,
            'healed' => $actualHeal,
            'cost' => $actualCost,
            'message' => "You were healed for {$actualHeal} HP at a cost of $" . number_format($actualCost) . ".",
        ];
    }

    /**
     * Heal player to full health
     */
    public function healFull(User $player): array
    {
        $healthNeeded = $player->max_health - $player->health;
        return $this->heal($player, $healthNeeded);
    }
}
