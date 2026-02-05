<?php

namespace App\Plugins\Combat\Services;

use App\Core\Models\User;
use App\Core\Models\Item;
use App\Plugins\Combat\Models\CombatLocation;
use App\Plugins\Combat\Models\CombatArea;
use App\Plugins\Combat\Models\CombatEnemy;
use App\Plugins\Combat\Models\CombatFight;
use App\Plugins\Combat\Models\CombatFightLog;
use App\Plugins\Inventory\Models\UserInventory;
use App\Plugins\Inventory\Models\UserEquipment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NPCCombatService
{
    /**
     * Start a hunt (spawn NPC enemy)
     */
    public function startHunt(User $user, int $locationId, int $areaId): array
    {
        $location = CombatLocation::findOrFail($locationId);
        $area = CombatArea::where('id', $areaId)
            ->where('location_id', $locationId)
            ->firstOrFail();

        // Check requirements
        if ($user->level < $location->min_level) {
            throw new \Exception("You need to be level {$location->min_level} to hunt here.");
        }

        if ($user->level < $area->min_level) {
            throw new \Exception("You need to be level {$area->min_level} to hunt in this area.");
        }

        if ($user->energy < $location->energy_cost) {
            throw new \Exception("You need {$location->energy_cost} energy to hunt here.");
        }

        if ($user->health <= 0) {
            throw new \Exception("You are too injured to hunt.");
        }

        // Check if user already has an active fight
        $activeFight = CombatFight::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($activeFight) {
            throw new \Exception("You are already in combat. Finish your current fight first.");
        }

        // Get random enemy from this area
        $enemy = CombatEnemy::where('area_id', $areaId)
            ->where('active', true)
            ->inRandomOrder()
            ->first();

        if (!$enemy) {
            throw new \Exception("No enemies found in this area.");
        }

        // Consume energy
        $user->decrement('energy', $location->energy_cost);

        // Create fight
        $fight = CombatFight::create([
            'user_id' => $user->id,
            'enemy_id' => $enemy->id,
            'area_id' => $areaId,
            'enemy_health' => $enemy->health,
            'enemy_max_health' => $enemy->max_health,
            'player_health_start' => $user->health,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(10),
            'status' => 'active',
        ]);

        // Get user's weapons and equipment
        $weapons = $this->getUserWeapons($user);
        $equipment = $this->getUserEquipment($user);

        return [
            'fight_id' => $fight->id,
            'enemy' => [
                'id' => $enemy->id,
                'name' => $enemy->name,
                'icon' => $enemy->icon,
                'level' => $enemy->level,
                'health' => $enemy->health,
                'max_health' => $enemy->max_health,
                'strength' => $enemy->strength,
                'defense' => $enemy->defense,
                'speed' => $enemy->speed,
                'agility' => $enemy->agility,
                'weakness' => $enemy->weakness,
                'difficulty' => $enemy->difficulty,
            ],
            'player' => [
                'health' => $user->health,
                'max_health' => $user->max_health,
                'energy' => $user->energy,
                'max_energy' => $user->max_energy,
            ],
            'weapons' => $weapons,
            'equipment' => $equipment,
            'expires_at' => $fight->expires_at->toISOString(),
        ];
    }

    /**
     * Attack NPC enemy
     */
    public function attackNPC(User $user, int $fightId, ?int $weaponId = null): array
    {
        $fight = CombatFight::with(['enemy', 'area'])
            ->where('id', $fightId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check fight status
        if ($fight->status !== 'active') {
            throw new \Exception("This fight is no longer active.");
        }

        if ($fight->expires_at < now()) {
            $fight->update(['status' => 'expired']);
            throw new \Exception("This fight has expired.");
        }

        if ($user->health <= 0) {
            $fight->update(['status' => 'lost']);
            throw new \Exception("You are too injured to continue fighting.");
        }

        if ($fight->enemy_health <= 0) {
            $fight->update(['status' => 'won']);
            throw new \Exception("Enemy is already defeated.");
        }

        $weapon = null;
        if ($weaponId) {
            $weapon = UserEquipment::with('item')
                ->where('user_id', $user->id)
                ->where('item_id', $weaponId)
                ->first();

            if (!$weapon) {
                throw new \Exception("Weapon not found or not equipped.");
            }
        }

        // Calculate player damage
        $playerDamage = $this->calculatePlayerDamage($user, $fight->enemy, $weapon);
        $playerCritical = $this->isCriticalHit($user->speed, $fight->enemy->agility);

        if ($playerCritical) {
            $playerDamage = (int) ($playerDamage * 1.5);
        }

        // Apply damage to enemy
        $fight->enemy_health = max(0, $fight->enemy_health - $playerDamage);

        // Log player attack
        $playerMessage = $weapon
            ? "You attack with {$weapon->item->name} for {$playerDamage} damage"
            : "You punch for {$playerDamage} damage";

        if ($playerCritical) {
            $playerMessage .= " (Critical Hit!)";
        }

        CombatFightLog::create([
            'fight_id' => $fight->id,
            'attacker_type' => 'player',
            'damage' => $playerDamage,
            'critical' => $playerCritical,
            'missed' => false,
            'weapon_used' => $weapon ? $weapon->item->name : 'Fists',
            'message' => $playerMessage,
        ]);

        $fightEnded = false;
        $result = [];

        // Check if enemy is defeated
        if ($fight->enemy_health <= 0) {
            $fight->status = 'won';
            $fightEnded = true;
            $result = $this->handleVictory($user, $fight);
        } else {
            // Enemy attacks back
            $enemyDamage = $this->calculateEnemyDamage($fight->enemy, $user);
            $enemyCritical = $this->isCriticalHit($fight->enemy->speed, $user->speed);

            if ($enemyCritical) {
                $enemyDamage = (int) ($enemyDamage * 1.5);
            }

            // Apply damage to player
            $user->health = max(0, $user->health - $enemyDamage);
            $user->save();

            // Log enemy attack
            $enemyMessage = "{$fight->enemy->name} attacks for {$enemyDamage} damage";
            if ($enemyCritical) {
                $enemyMessage .= " (Critical Hit!)";
            }

            CombatFightLog::create([
                'fight_id' => $fight->id,
                'attacker_type' => 'enemy',
                'damage' => $enemyDamage,
                'critical' => $enemyCritical,
                'missed' => false,
                'weapon_used' => null,
                'message' => $enemyMessage,
            ]);

            // Check if player is defeated
            if ($user->health <= 0) {
                $fight->status = 'lost';
                $fightEnded = true;
                $result = ['message' => 'You have been defeated!'];
            }
        }

        $fight->save();

        return [
            'fight_ended' => $fightEnded,
            'player_damage' => $playerDamage,
            'enemy_damage' => $fightEnded && $fight->status === 'won' ? 0 : ($enemyDamage ?? 0),
            'player_health' => $user->health,
            'player_max_health' => $user->max_health,
            'enemy_health' => $fight->enemy_health,
            'enemy_max_health' => $fight->enemy->max_health,
            'status' => $fight->status,
            'logs' => [
                $playerMessage,
                ...(isset($enemyMessage) ? [$enemyMessage] : [])
            ],
            ...$result,
        ];
    }

    /**
     * Auto attack NPC (3-5 attacks)
     */
    public function autoAttackNPC(User $user, int $fightId): array
    {
        $attacks = rand(3, 5);
        $logs = [];
        $totalPlayerDamage = 0;
        $totalEnemyDamage = 0;

        for ($i = 0; $i < $attacks; $i++) {
            try {
                $result = $this->attackNPC($user, $fightId);
                $logs = array_merge($logs, $result['logs']);
                $totalPlayerDamage += $result['player_damage'];
                $totalEnemyDamage += $result['enemy_damage'];

                if ($result['fight_ended']) {
                    return [
                        'attacks_made' => $i + 1,
                        'total_player_damage' => $totalPlayerDamage,
                        'total_enemy_damage' => $totalEnemyDamage,
                        'logs' => $logs,
                        'fight_ended' => true,
                        'status' => $result['status'],
                        'player_health' => $result['player_health'],
                        'enemy_health' => $result['enemy_health'],
                        ...(isset($result['rewards']) ? ['rewards' => $result['rewards']] : []),
                        ...(isset($result['message']) ? ['message' => $result['message']] : []),
                    ];
                }
            } catch (\Exception $e) {
                break;
            }
        }

        return [
            'attacks_made' => $attacks,
            'total_player_damage' => $totalPlayerDamage,
            'total_enemy_damage' => $totalEnemyDamage,
            'logs' => $logs,
            'fight_ended' => false,
        ];
    }

    /**
     * Handle victory rewards
     */
    private function handleVictory(User $user, CombatFight $fight): array
    {
        $enemy = $fight->enemy;

        // Calculate rewards
        $experience = $enemy->experience_reward;
        $cash = rand($enemy->cash_reward_min, $enemy->cash_reward_max);

        // Award rewards
        $user->increment('experience', $experience);
        $user->increment('cash', $cash);

        // Check for level up
        $leveledUp = false;
        $newLevel = $user->level;
        while ($user->experience >= $this->getExperienceRequired($user->level)) {
            $user->experience -= $this->getExperienceRequired($user->level);
            $user->increment('level');
            $leveledUp = true;
            $newLevel = $user->level;
        }

        $user->save();

        return [
            'message' => "Victory! You defeated {$enemy->name}!",
            'rewards' => [
                'experience' => $experience,
                'cash' => $cash,
            ],
            'leveled_up' => $leveledUp,
            'new_level' => $newLevel,
        ];
    }

    /**
     * Calculate player damage
     */
    private function calculatePlayerDamage(User $user, CombatEnemy $enemy, ?UserEquipment $weapon = null): int
    {
        $baseDamage = $user->strength;

        if ($weapon) {
            $baseDamage += $weapon->item->power ?? 0;
        }

        // Apply enemy defense
        $damage = max(1, $baseDamage - $enemy->defense);

        // Add randomness (±20%)
        $variance = $damage * 0.2;
        $damage += rand(-$variance, $variance);

        return max(1, (int) $damage);
    }

    /**
     * Calculate enemy damage
     */
    private function calculateEnemyDamage(CombatEnemy $enemy, User $user): int
    {
        $baseDamage = $enemy->strength;

        // Apply player defense
        $damage = max(1, $baseDamage - $user->defense);

        // Add randomness (±20%)
        $variance = $damage * 0.2;
        $damage += rand(-$variance, $variance);

        return max(1, (int) $damage);
    }

    /**
     * Check for critical hit
     */
    private function isCriticalHit(int $attackerSpeed, int $defenderAgility): bool
    {
        $critChance = max(5, min(25, ($attackerSpeed - $defenderAgility) * 2 + 10));
        return rand(1, 100) <= $critChance;
    }

    /**
     * Get user's equipped weapons
     */
    private function getUserWeapons(User $user): array
    {
        return UserEquipment::with('item')
            ->where('user_id', $user->id)
            ->whereHas('item', function ($query) {
                $query->where('type', 'weapon');
            })
            ->get()
            ->map(function ($userEquipment) {
                return [
                    'id' => $userEquipment->item->id,
                    'name' => $userEquipment->item->name,
                    'power' => $userEquipment->item->power ?? 0,
                    'equipped' => true,
                    'slot' => $userEquipment->slot,
                ];
            })
            ->toArray();
    }

    /**
     * Get user's equipped equipment
     */
    private function getUserEquipment(User $user): array
    {
        return UserEquipment::with('item')
            ->where('user_id', $user->id)
            ->whereHas('item', function ($query) {
                $query->whereIn('type', ['armor', 'accessory']);
            })
            ->get()
            ->map(function ($userEquipment) {
                return [
                    'id' => $userEquipment->item->id,
                    'name' => $userEquipment->item->name,
                    'type' => $userEquipment->item->type,
                    'defense' => $userEquipment->item->defense ?? 0,
                    'slot' => $userEquipment->slot,
                ];
            })
            ->toArray();
    }

    /**
     * Get experience required for next level
     */
    private function getExperienceRequired(int $level): int
    {
        return $level * 100; // Simple formula: level * 100
    }
}
