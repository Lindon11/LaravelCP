<?php

namespace App\Services;

use App\Models\User;
use App\Models\CombatLog;
use App\Models\Item;
use Exception;

class CombatService
{
    protected ItemEffectsService $itemEffectsService;

    public function __construct(ItemEffectsService $itemEffectsService)
    {
        $this->itemEffectsService = $itemEffectsService;
    }

    /**
     * Get online users available for attack
     */
    public function getAvailableTargets(User $attacker)
    {
        return User::where('id', '!=', $attacker->id)
            ->where('health', '>', 0)
            ->whereNull('jail_until')
            ->where('updated_at', '>=', now()->subMinutes(15)) // Active in last 15 mins
            ->orderBy('level', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'level' => $user->level,
                    'rank' => $user->rank,
                    'health' => $user->health,
                    'max_health' => $user->max_health,
                ];
            });
    }

    /**
     * Get user's combat history
     */
    public function getCombatHistory(User $user, $limit = 20)
    {
        return CombatLog::with(['attacker', 'defender'])
            ->where(function ($query) use ($user) {
                $query->where('attacker_id', $user->id)
                    ->orWhere('defender_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Attack another user
     */
    public function attackPlayer(User $attacker, int $defenderId)
    {
        // Validation checks
        if ($attacker->health <= 0) {
            throw new Exception('You are dead! Visit the hospital first.');
        }

        if ($attacker->jail_until && now() < $attacker->jail_until) {
            throw new Exception('You cannot attack while in jail!');
        }

        $defender = User::findOrFail($defenderId);

        if ($defender->id === $attacker->id) {
            throw new Exception('You cannot attack yourself!');
        }

        if ($defender->health <= 0) {
            throw new Exception('Target is already dead!');
        }

        if ($defender->jail_until && now() < $defender->jail_until) {
            throw new Exception('Target is in jail!');
        }

        // Get equipment bonuses
        $attackerBonuses = $this->itemEffectsService->getEquipmentBonuses($attacker);
        $defenderBonuses = $this->itemEffectsService->getEquipmentBonuses($defender);

        // Store health before combat
        $attackerHealthBefore = $attacker->health;
        $defenderHealthBefore = $defender->health;

        // Calculate combat stats with equipment bonuses
        $attackerStrength = ($attacker->strength ?? 10) + $attackerBonuses['strength'];
        $attackerDamage = $attackerBonuses['damage'];
        $attackerAccuracy = $this->itemEffectsService->getAccuracy($attacker);

        $defenderDefense = ($defender->defense ?? 10) + $defenderBonuses['defense'];
        $defenderEvasion = $this->itemEffectsService->getEvasion($defender);

        // Determine if attack hits
        $levelDifference = $attacker->level - $defender->level;
        $hitChance = $attackerAccuracy - $defenderEvasion + ($levelDifference * 0.01); // ±1% per level
        $hitChance = max(0.40, min(0.95, $hitChance)); // Clamp between 40-95%

        $hit = (rand(1, 100) / 100) <= $hitChance;

        if (!$hit) {
            // Attack missed - defender counterattacks
            $counterDamage = $this->calculateDamage($defender, $attacker, $defenderBonuses, $attackerBonuses);
            $attacker->health = max(0, $attacker->health - $counterDamage);
            $attacker->save();

            CombatLog::create([
                'attacker_id' => $attacker->id,
                'defender_id' => $defender->id,
                'damage_dealt' => 0,
                'attacker_health_before' => $attackerHealthBefore,
                'attacker_health_after' => $attacker->health,
                'defender_health_before' => $defenderHealthBefore,
                'defender_health_after' => $defender->health,
                'outcome' => 'failed',
                'respect_gained' => 0,
                'cash_stolen' => 0,
                'defender_in_hospital' => false,
            ]);

            throw new Exception("Your attack missed! {$defender->username} countered for {$counterDamage} damage!");
        }

        // Calculate damage with equipment bonuses
        $damage = $this->calculateDamage($attacker, $defender, $attackerBonuses, $defenderBonuses);
        $defender->health = max(0, $defender->health - $damage);

        // Determine outcome
        $killed = $defender->health <= 0;
        $respectGained = 0;
        $cashStolen = 0;

        if ($killed) {
            // User killed - steal cash and respect
            $cashStolen = (int) ($defender->cash * 0.1); // Steal 10% of cash
            $respectGained = max(1, (int) ($defender->respect * 0.05)); // Steal 5% of respect

            $attacker->cash += $cashStolen;
            $attacker->respect += $respectGained;

            $defender->cash -= $cashStolen;
            $defender->respect -= $respectGained;
            $defender->health = 0;

            $outcome = 'killed';
        } else {
            // User survived
            $respectGained = rand(1, 3);
            $cashStolen = rand(100, 500);

            $attacker->cash += $cashStolen;
            $defender->cash = max(0, $defender->cash - $cashStolen);

            $outcome = 'success';
        }

        // Save users
        $attacker->save();
        $defender->save();

        // Log combat
        CombatLog::create([
            'attacker_id' => $attacker->id,
            'defender_id' => $defender->id,
            'damage_dealt' => $damage,
            'attacker_health_before' => $attackerHealthBefore,
            'attacker_health_after' => $attacker->health,
            'defender_health_before' => $defenderHealthBefore,
            'defender_health_after' => $defender->health,
            'outcome' => $outcome,
            'respect_gained' => $respectGained,
            'cash_stolen' => $cashStolen,
            'defender_in_hospital' => $killed,
        ]);

        return [
            'success' => true,
            'damage' => $damage,
            'killed' => $killed,
            'attacker_health' => $attacker->health,
            'defender_health' => $defender->health,
            'respect_gained' => $respectGained,
            'cash_stolen' => $cashStolen,
        ];
    }

    /**
     * Calculate damage dealt with equipment bonuses
     */
    private function calculateDamage(User $attacker, User $defender, array $attackerBonuses, array $defenderBonuses): int
    {
        // Base stats
        $attackerStrength = ($attacker->strength ?? 10) + $attackerBonuses['strength'];
        $attackerDamage = $attackerBonuses['damage'];
        $defenderDefense = ($defender->defense ?? 10) + $defenderBonuses['defense'];

        // Base damage calculation
        $baseDamage = ($attackerStrength * 2) + $attackerDamage - $defenderDefense;

        // Add level bonus
        $levelBonus = $attacker->level * 0.5;
        $baseDamage += $levelBonus;

        // Add randomness (±20%)
        $randomness = rand(80, 120) / 100;
        $damage = (int) ($baseDamage * $randomness);

        // Minimum 10 damage
        return max(10, $damage);
    }
}

