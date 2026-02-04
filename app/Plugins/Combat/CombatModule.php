<?php

namespace App\Plugins\Combat;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Combat\Models\CombatLog;

/**
 * Combat Module
 * 
 * Handles PvP combat system - attack other players for cash and respect
 * Includes damage calculation, death mechanics, and combat history
 */
class CombatModule extends Plugin
{
    protected string $name = 'Combat';
    
    public function construct(): void
    {
        $this->config = [
            'cooldown' => 30, // seconds between attacks
            'energy_cost' => 5,
            'death_penalty' => 0.10, // 10% cash loss on death
            'min_cash_steal' => 100,
            'max_cash_steal_percentage' => 0.20, // 20% of target's cash
        ];
    }
    
    /**
     * Get available targets for combat
     */
    public function getAvailableTargets(User $user, int $limit = 20): array
    {
        return User::where('id', '!=', $user->id)
            ->where('level', '>=', max(1, $user->level - 5))
            ->where('level', '<=', $user->level + 5)
            ->whereNull('jail_until')
            ->orWhere('jail_until', '<', now())
            ->where('hospital_until', '<', now())
            ->orWhereNull('hospital_until')
            ->limit($limit)
            ->get()
            ->map(function ($target) use ($user) {
                return $this->applyModuleHook('alterCombatTarget', [
                    'target' => $target,
                    'user' => $user,
                    'win_chance' => $this->calculateWinChance($user, $target),
                ]);
            })
            ->toArray();
    }
    
    /**
     * Attack another player
     */
    public function attackPlayer(User $attacker, User $defender): array
    {
        // Validation
        if ($attacker->id === $defender->id) {
            return $this->error('You cannot attack yourself');
        }
        
        // Check cooldown
        if ($attacker->hasTimer('combat')) {
            return $this->error('You must wait before attacking again');
        }
        
        // Check energy
        if ($attacker->energy < $this->config['energy_cost']) {
            return $this->error('Not enough energy');
        }
        
        // Check if defender is in jail/hospital
        if ($defender->jail_until && now()->lt($defender->jail_until)) {
            return $this->error('Target is in jail');
        }
        
        if ($defender->hospital_until && now()->lt($defender->hospital_until)) {
            return $this->error('Target is in hospital');
        }
        
        // Apply hooks before combat
        $this->applyModuleHook('beforeCombat', [
            'attacker' => $attacker,
            'defender' => $defender,
        ]);
        
        // Calculate combat outcome
        $attackerPower = $this->calculateCombatPower($attacker);
        $defenderPower = $this->calculateCombatPower($defender);
        
        $attackerWins = $attackerPower > $defenderPower || 
                        ($attackerPower === $defenderPower && rand(0, 1) === 1);
        
        $result = [];
        
        if ($attackerWins) {
            // Calculate damage and cash stolen
            $damage = rand(10, 50) + ($attackerPower - $defenderPower);
            $cashStolen = min(
                floor($defender->cash * $this->config['max_cash_steal_percentage']),
                max($this->config['min_cash_steal'], rand(100, 1000))
            );
            
            // Check if killed
            $defender->health -= $damage;
            $killed = $defender->health <= 0;
            
            if ($killed) {
                // Death mechanics
                $defender->health = 100;
                $defender->hospital_until = now()->addMinutes(10);
                $defender->cash = floor($defender->cash * (1 - $this->config['death_penalty']));
                
                // Attacker gains respect
                $respectGained = rand(1, 5);
                $attacker->respect += $respectGained;
                
                $result = $this->success("You killed {$defender->username}! Gained {$respectGained} respect and {$this->money($cashStolen)}!");
                $result['killed'] = true;
                $result['respect_gained'] = $respectGained;
            } else {
                $result = $this->success("You dealt {$damage} damage and stole {$this->money($cashStolen)}!");
                $result['killed'] = false;
                $result['damage'] = $damage;
            }
            
            // Transfer cash
            $attacker->cash += $cashStolen;
            $defender->cash -= $cashStolen;
            
            $result['cash_stolen'] = $cashStolen;
            $result['winner'] = 'attacker';
        } else {
            // Defender wins
            $damage = rand(5, 25) + ($defenderPower - $attackerPower);
            $attacker->health -= $damage;
            
            // Check if attacker killed
            if ($attacker->health <= 0) {
                $attacker->health = 100;
                $attacker->hospital_until = now()->addMinutes(5);
                $result = $this->error("You were defeated and sent to the hospital!");
                $result['killed'] = true;
            } else {
                $result = $this->error("You lost! Took {$damage} damage");
                $result['killed'] = false;
            }
            
            $result['damage'] = $damage;
            $result['winner'] = 'defender';
        }
        
        // Deduct energy
        $attacker->energy -= $this->config['energy_cost'];
        
        // Set cooldown
        $attacker->setTimer('combat', $this->config['cooldown']);
        
        // Save both players
        $attacker->save();
        $defender->save();
        
        // Log combat
        CombatLog::create([
            'attacker_id' => $attacker->id,
            'defender_id' => $defender->id,
            'winner' => $result['winner'],
            'damage' => $result['damage'] ?? 0,
            'cash_stolen' => $result['cash_stolen'] ?? 0,
            'killed' => $result['killed'] ?? false,
        ]);
        
        // Track action
        $this->trackAction($attacker, 'combat_attack', [
            'defender_id' => $defender->id,
            'winner' => $result['winner'],
        ]);
        
        // Fire hook after combat
        $this->applyModuleHook('afterCombat', [
            'attacker' => $attacker,
            'defender' => $defender,
            'result' => $result,
        ]);
        
        return $result;
    }
    
    /**
     * Calculate combat power based on stats and equipment
     */
    protected function calculateCombatPower(User $user): int
    {
        $basePower = $user->strength + $user->defense + $user->speed;
        
        // Add equipment bonuses
        $equipmentBonus = 0;
        if ($user->equippedItems) {
            foreach ($user->equippedItems as $equipped) {
                $equipmentBonus += $equipped->item->attack ?? 0;
                $equipmentBonus += $equipped->item->defense ?? 0;
            }
        }
        
        $totalPower = $basePower + $equipmentBonus;
        
        // Apply module hooks
        $totalPower = $this->applyModuleHook('modifyCombatPower', [
            'user' => $user,
            'base_power' => $basePower,
            'equipment_bonus' => $equipmentBonus,
            'total_power' => $totalPower,
        ])['total_power'] ?? $totalPower;
        
        return $totalPower;
    }
    
    /**
     * Calculate win chance against target
     */
    protected function calculateWinChance(User $attacker, User $defender): float
    {
        $attackerPower = $this->calculateCombatPower($attacker);
        $defenderPower = $this->calculateCombatPower($defender);
        
        $powerDiff = $attackerPower - $defenderPower;
        $winChance = 0.5 + ($powerDiff / 1000);
        
        return min(0.95, max(0.05, $winChance));
    }
    
    /**
     * Get combat history
     */
    public function getCombatHistory(User $user, int $limit = 15): array
    {
        return CombatLog::where('attacker_id', $user->id)
            ->orWhere('defender_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
