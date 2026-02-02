<?php

namespace App\Services;

use App\Models\User;
use App\Models\KillLog;
use Carbon\Carbon;

class KillService
{
    /**
     * Check if player is alive
     */
    public function isAlive(User $player): bool
    {
        if (!$player->died_at) {
            return true;
        }

        // Players respawn after 5 minutes
        $respawnTime = 300; // 5 minutes
        return $player->died_at->addSeconds($respawnTime)->isPast();
    }

    /**
     * Get time until respawn
     */
    public function getTimeUntilRespawn(User $player): int
    {
        if (!$player->died_at) {
            return 0;
        }

        $respawnTime = 300;
        $respawnAt = $player->died_at->addSeconds($respawnTime);
        
        if ($respawnAt->isPast()) {
            return 0;
        }

        return now()->diffInSeconds($respawnAt, false);
    }

    /**
     * Respawn a dead player
     */
    public function respawn(User $player): void
    {
        $player->died_at = null;
        $player->killed_by = null;
        $player->health = $player->max_health;
        $player->save();
    }

    /**
     * Calculate attack power (simplified from legacy)
     */
    protected function calculateAttackPower(User $attacker): int
    {
        // Base power from level + respect
        return ($attacker->level * 10) + ($attacker->respect / 10);
    }

    /**
     * Calculate defense power (simplified from legacy)
     */
    protected function calculateDefensePower(User $defender): int
    {
        // Base defense from level + health percentage
        $healthPercent = ($defender->health / $defender->max_health);
        return ($defender->level * 8) + ($healthPercent * 50);
    }

    /**
     * Attempt to kill another player
     */
    public function attemptKill(User $attacker, User $victim, int $bulletsUsed): array
    {
        // Validation
        if ($attacker->id === $victim->id) {
            return ['success' => false, 'message' => 'You cannot attack yourself!'];
        }

        if (!$this->isAlive($attacker)) {
            return ['success' => false, 'message' => 'You are dead! Wait to respawn.'];
        }

        if (!$this->isAlive($victim)) {
            return ['success' => false, 'message' => 'This player is already dead!'];
        }

        if ($attacker->bullets < $bulletsUsed) {
            return ['success' => false, 'message' => 'You do not have enough bullets!'];
        }

        if ($bulletsUsed <= 0) {
            return ['success' => false, 'message' => 'You must use at least 1 bullet!'];
        }

        // Calculate damage (legacy formula simplified)
        $attackPower = $this->calculateAttackPower($attacker);
        $defensePower = $this->calculateDefensePower($victim);
        
        $damage = floor(($attackPower / $defensePower) * $bulletsUsed * 10);

        // Deduct bullets
        $attacker->bullets -= $bulletsUsed;
        $attacker->save();

        // Apply damage
        $victim->health -= $damage;

        $success = false;
        
        if ($victim->health <= 0) {
            // Kill successful!
            $success = true;
            $victim->health = 0;
            $victim->died_at = now();
            $victim->killed_by = $attacker->id;
            $victim->save();

            // Award respect to killer
            $attacker->respect += 5;
            $attacker->save();

            // Check for bounty
            $bountyResult = app(BountyService::class)->claimBounty($attacker, $victim);
            $bountyMessage = $bountyResult ? ' + ' . $bountyResult['message'] : '';

            $message = "You killed {$victim->username} using {$bulletsUsed} bullets!" . $bountyMessage;
        } else {
            // Damage dealt but not killed
            $victim->save();
            $message = "You shot {$victim->username} for {$damage} damage using {$bulletsUsed} bullets, but they survived!";
        }

        // Log the attempt
        KillLog::create([
            'killer_id' => $attacker->id,
            'victim_id' => $victim->id,
            'bullets_used' => $bulletsUsed,
            'damage_dealt' => $damage,
            'successful' => $success,
            'killed_at' => now(),
        ]);

        return [
            'success' => $success,
            'damage' => $damage,
            'message' => $message,
        ];
    }

    /**
     * Get online players to attack
     */
    public function getTargets(User $currentPlayer)
    {
        return User::with('user')
            ->where('id', '!=', $currentPlayer->id)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('last_active_at')
                    ->where('last_active_at', '>=', now()->subMinutes(15));
            })
            ->orderBy('level', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($player) {
                $isAlive = $this->isAlive($player);
                $respawnTime = $isAlive ? 0 : $this->getTimeUntilRespawn($player);

                return [
                    'id' => $player->id,
                    'username' => $player->username,
                    'level' => $player->level,
                    'rank' => $player->rank,
                    'health' => $player->health,
                    'max_health' => $player->max_health,
                    'respect' => $player->respect,
                    'is_alive' => $isAlive,
                    'respawn_time' => $respawnTime,
                ];
            });
    }
}
