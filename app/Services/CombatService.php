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

    /**
     * Start NPC fight in area
     */
    public function startNPCFight(User $player, int $areaId)
    {
        // Check if player has an active fight
        $activeFight = \App\Models\CombatFight::where('user_id', $player->id)
            ->where('status', 'active')
            ->first();

        if ($activeFight && !$activeFight->isExpired()) {
            throw new \Exception('You already have an active fight!');
        }

        // Get area with enemies
        $area = \App\Models\CombatArea::with('enemies')->findOrFail($areaId);

        // Check level requirement
        if ($player->level < $area->min_level) {
            throw new \Exception('You need to be level ' . $area->min_level . ' to hunt here!');
        }

        // Check player health
        if ($player->health <= 0) {
            throw new \Exception('You are dead! Visit the hospital first.');
        }

        // Spawn random enemy from area
        $enemies = $area->enemies()->where('active', true)->get();
        if ($enemies->isEmpty()) {
            throw new \Exception('No enemies found in this area!');
        }

        // Weighted random selection based on spawn_rate
        $totalWeight = $enemies->sum('spawn_rate');
        $random = mt_rand() / mt_getrandmax() * $totalWeight;
        $currentWeight = 0;
        $selectedEnemy = $enemies->first();

        foreach ($enemies as $enemy) {
            $currentWeight += $enemy->spawn_rate;
            if ($random <= $currentWeight) {
                $selectedEnemy = $enemy;
                break;
            }
        }

        // Create fight
        $fight = \App\Models\CombatFight::create([
            'user_id' => $player->id,
            'enemy_id' => $selectedEnemy->id,
            'area_id' => $areaId,
            'enemy_health' => $selectedEnemy->health,
            'enemy_max_health' => $selectedEnemy->max_health,
            'player_health_start' => $player->health,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(10),
            'status' => 'active',
        ]);

        // Get player's weapons
        $weapons = $this->getPlayerWeapons($player);

        // Get player's equipment with durability
        $equipment = $this->getPlayerEquipment($player);

        return [
            'fight' => $fight,
            'enemy' => [
                'id' => $selectedEnemy->id,
                'name' => $selectedEnemy->name,
                'level' => $selectedEnemy->level,
                'health' => $selectedEnemy->health,
                'max_health' => $selectedEnemy->max_health,
                'strength' => $selectedEnemy->strength,
                'defense' => $selectedEnemy->defense,
                'speed' => $selectedEnemy->speed,
                'agility' => $selectedEnemy->agility,
                'weakness' => $selectedEnemy->weakness,
                'difficulty' => $selectedEnemy->difficulty,
            ],
            'weapons' => $weapons,
            'equipment' => $equipment,
        ];
    }

    /**
     * Attack NPC
     */
    public function attackNPC(User $player, int $fightId, ?int $weaponId)
    {
        $fight = \App\Models\CombatFight::with('enemy')->findOrFail($fightId);

        // Validate fight
        if ($fight->user_id !== $player->id) {
            throw new \Exception('This is not your fight!');
        }

        if ($fight->status !== 'active') {
            throw new \Exception('This fight is no longer active!');
        }

        if ($fight->isExpired()) {
            $fight->update(['status' => 'fled']);
            throw new \Exception('Fight expired! You ran away.');
        }

        // Get weapon if specified
        $weapon = null;
        $weaponDamage = 0;
        $weaponName = 'Fists';

        if ($weaponId) {
            $weapon = Item::findOrFail($weaponId);
            // Check if player owns it
            $playerItem = $player->items()->where('item_id', $weaponId)->first();
            if (!$playerItem) {
                throw new \Exception('You don\'t own this weapon!');
            }

            // Check ammo for guns
            if ($weapon->type === 'weapon' && isset($weapon->stats['ammo_per_shot'])) {
                $ammoNeeded = $weapon->stats['ammo_per_shot'];
                if ($player->bullets < $ammoNeeded) {
                    throw new \Exception('Not enough bullets!');
                }
                $player->bullets -= $ammoNeeded;
            }

            $weaponDamage = $weapon->stats['damage'] ?? 10;
            $weaponName = $weapon->name;
        }

        // Calculate player damage
        $playerStrength = $player->strength ?? 10;
        $baseDamage = ($playerStrength * 2) + $weaponDamage;
        $randomness = rand(80, 120) / 100;
        $damage = max(5, (int)($baseDamage * $randomness));

        // Check for critical hit (10% chance)
        $critical = rand(1, 100) <= 10;
        if ($critical) {
            $damage *= 2;
        }

        // Apply damage to enemy
        $fight->enemy_health = max(0, $fight->enemy_health - $damage);
        $fight->save();

        // Log the attack
        $message = $player->username . ' attacked ' . $fight->enemy->name . ' with ' . $weaponName . ' and dealt ' . $damage . ' damage';
        if ($critical) {
            $message .= ' (CRITICAL HIT!)';
        }

        \App\Models\CombatFightLog::create([
            'fight_id' => $fight->id,
            'attacker_type' => 'player',
            'damage' => $damage,
            'critical' => $critical,
            'missed' => false,
            'weapon_used' => $weaponName,
            'message' => $message,
        ]);

        // Check if enemy died
        if ($fight->enemy_health <= 0) {
            $fight->update(['status' => 'won']);

            // Give rewards
            $expReward = $fight->enemy->experience_reward;
            $cashReward = rand($fight->enemy->cash_reward_min, $fight->enemy->cash_reward_max);

            $player->experience += $expReward;
            $player->cash += $cashReward;
            $player->save();

            return [
                'result' => 'hit',
                'message' => $message,
                'enemy' => ['health' => 0, 'max_health' => $fight->enemy_max_health],
                'player' => ['health' => $player->health, 'max_health' => $player->max_health],
                'ended' => true,
                'end_message' => 'Victory! You defeated ' . $fight->enemy->name . '!',
                'rewards' => [
                    'experience' => $expReward,
                    'cash' => $cashReward,
                ],
            ];
        }

        // Enemy counterattack
        $enemyDamage = max(5, (int)(($fight->enemy->strength * 2) * (rand(80, 120) / 100)));
        $player->health = max(0, $player->health - $enemyDamage);
        $player->save();

        $counterMessage = $fight->enemy->name . ' countered for ' . $enemyDamage . ' damage';
        \App\Models\CombatFightLog::create([
            'fight_id' => $fight->id,
            'attacker_type' => 'enemy',
            'damage' => $enemyDamage,
            'critical' => false,
            'missed' => false,
            'weapon_used' => null,
            'message' => $counterMessage,
        ]);

        // Check if player died
        if ($player->health <= 0) {
            $fight->update(['status' => 'lost']);
            return [
                'result' => 'hit',
                'message' => $message . "\n" . $counterMessage,
                'enemy' => ['health' => $fight->enemy_health, 'max_health' => $fight->enemy_max_health],
                'player' => ['health' => 0, 'max_health' => $player->max_health],
                'ended' => true,
                'end_message' => 'You were defeated by ' . $fight->enemy->name . '! Visit the hospital.',
            ];
        }

        return [
            'result' => $critical ? 'critical' : 'hit',
            'message' => $message . "\n" . $counterMessage,
            'enemy' => ['health' => $fight->enemy_health, 'max_health' => $fight->enemy_max_health],
            'player' => ['health' => $player->health, 'max_health' => $player->max_health],
            'ended' => false,
        ];
    }

    /**
     * Auto attack NPC
     */
    public function autoAttackNPC(User $player, int $fightId)
    {
        $logs = [];
        $ended = false;
        $endMessage = null;
        $rewards = null;

        // Attack 3-5 times
        $attacks = rand(3, 5);
        for ($i = 0; $i < $attacks; $i++) {
            try {
                $result = $this->attackNPC($player, $fightId, null);
                $logs[] = ['type' => 'player', 'message' => $result['message']];

                if ($result['ended']) {
                    $ended = true;
                    $endMessage = $result['end_message'];
                    $rewards = $result['rewards'] ?? null;
                    break;
                }
            } catch (\Exception $e) {
                break;
            }
        }

        $fight = \App\Models\CombatFight::findOrFail($fightId);

        return [
            'logs' => $logs,
            'enemy' => ['health' => $fight->enemy_health, 'max_health' => $fight->enemy_max_health],
            'player' => ['health' => $player->health, 'max_health' => $player->max_health],
            'ended' => $ended,
            'end_message' => $endMessage,
            'rewards' => $rewards,
        ];
    }

    /**
     * Get player's available weapons
     */
    private function getPlayerWeapons(User $player): array
    {
        $weapons = [];

        // Always include fists
        $weapons[] = [
            'id' => null,
            'name' => 'Fists',
            'icon' => '👊',
            'damage' => 5,
            'accuracy' => 75,
        ];

        // Get player's weapon items
        $playerWeapons = $player->items()
            ->whereHas('item', function ($query) {
                $query->where('type', 'weapon');
            })
            ->with('item')
            ->get();

        foreach ($playerWeapons as $playerItem) {
            $item = $playerItem->item;
            $weapons[] = [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->stats['icon'] ?? '🔫',
                'damage' => $item->stats['damage'] ?? 10,
                'accuracy' => $item->stats['accuracy'] ?? 80,
                'ammo' => isset($item->stats['ammo_per_shot']) ? [
                    'current' => $player->bullets,
                    'max' => 999,
                    'per_shot' => $item->stats['ammo_per_shot'],
                ] : null,
                'durability' => $playerItem->stats['durability'] ?? 100,
            ];
        }

        return $weapons;
    }

    /**
     * Get player's equipment
     */
    private function getPlayerEquipment(User $player): array
    {
        $slots = ['helmet', 'armor', 'gloves', 'boots'];
        $equipment = [];

        foreach ($slots as $slot) {
            $item = $player->items()
                ->whereHas('item', function ($query) use ($slot) {
                    $query->where('type', $slot);
                })
                ->with('item')
                ->first();

            if ($item) {
                $equipment[] = [
                    'slot' => $slot,
                    'icon' => $item->item->stats['icon'] ?? '🛡️',
                    'durability' => $item->stats['durability'] ?? 100,
                ];
            }
        }

        return $equipment;
    }
}

