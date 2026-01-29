<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class JailService
{
    /**
     * Check if a player is in jail
     */
    public function isInJail(User $player): bool
    {
        return $player->jail_until && $player->jail_until->isFuture();
    }

    /**
     * Check if a player is in super max (more secure jail)
     */
    public function isInSuperMax(User $player): bool
    {
        return $player->super_max_until && $player->super_max_until->isFuture();
    }

    /**
     * Get remaining jail time in seconds
     */
    public function getRemainingTime(User $player): int
    {
        if (!$this->isInJail($player)) {
            return 0;
        }

        return now()->diffInSeconds($player->jail_until, false);
    }

    /**
     * Send a player to jail
     */
    public function sendToJail(User $player, int $seconds): void
    {
        $jailTime = now()->addSeconds($seconds);
        $player->jail_until = $jailTime;
        $player->save();
    }

    /**
     * Send a player to super max
     */
    public function sendToSuperMax(User $player, int $seconds): void
    {
        $jailTime = now()->addSeconds($seconds);
        $player->jail_until = $jailTime;
        $player->super_max_until = $jailTime;
        $player->save();
    }

    /**
     * Release a player from jail
     */
    public function release(User $player): void
    {
        $player->jail_until = null;
        $player->super_max_until = null;
        $player->save();
    }

    /**
     * Get all players currently in jail (same location)
     */
    public function getJailedPlayers(?User $currentPlayer = null): array
    {
        $query = User::with('user')
            ->whereNotNull('jail_until')
            ->where('jail_until', '>', now())
            ->orderBy('rank')
            ->orderBy('username');

        $players = $query->get();

        return $players->map(function ($player) use ($currentPlayer) {
            $inJail = $currentPlayer ? $this->isInJail($currentPlayer) : false;
            $inSuperMax = $currentPlayer ? $this->isInSuperMax($currentPlayer) : false;
            $targetInSuperMax = $this->isInSuperMax($player);

            // Calculate bust-out success chance based on legacy formula
            if ($targetInSuperMax) {
                $percent = 0; // Can't bust out from super max
            } else if ($inSuperMax) {
                $percent = 0; // Can't bust out while in super max
            } else if ($inJail) {
                // If you're in jail, chances are halved
                $percent = $player->level > 16 ? 5 : (95 - ($player->level * 5)) / 2;
            } else {
                // Normal bust-out chance
                $percent = $player->level > 16 ? 10 : (95 - ($player->level * 5));
            }

            return [
                'id' => $player->id,
                'username' => $player->username,
                'user_id' => $player->user_id,
                'level' => $player->level,
                'rank' => $player->rank,
                'jail_until' => $player->jail_until,
                'super_max_until' => $player->super_max_until,
                'is_super_max' => $targetInSuperMax,
                'time_remaining' => $this->getRemainingTime($player),
                'bust_chance' => $percent,
                'is_current_user' => $currentPlayer && $player->id === $currentPlayer->id,
            ];
        })->toArray();
    }

    /**
     * Attempt to bust a player out of jail
     */
    public function attemptBustOut(User $actor, User $target): array
    {
        // Check if actor is in super max
        if ($this->isInSuperMax($actor)) {
            return [
                'success' => false,
                'message' => "You can't bust anyone out while you are in super max!",
            ];
        }

        // Check if target is in jail
        if (!$this->isInJail($target)) {
            return [
                'success' => false,
                'message' => "This player is not in jail.",
            ];
        }

        // Check if target is in super max
        if ($this->isInSuperMax($target)) {
            return [
                'success' => false,
                'message' => "This player is in super max and cannot be busted out.",
            ];
        }

        $actorInJail = $this->isInJail($actor);

        // Calculate success chance
        if ($target->level > 16) {
            $successChance = $actorInJail ? 5 : 10;
        } else {
            $successChance = $actorInJail ? (95 - ($target->level * 5)) / 2 : (95 - ($target->level * 5));
        }

        $roll = mt_rand(1, 100);
        $targetName = $actor->id === $target->id ? 'yourself' : $target->username;

        if ($successChance >= $roll) {
            // Success! Free the target
            $this->release($target);
            
            // Award experience
            $actor->respect += 1;
            $actor->save();

            return [
                'success' => true,
                'message' => "You successfully broke {$targetName} out of jail!",
                'respect_gained' => 1,
            ];
        } else {
            // Failed! Actor goes to jail or gets more time
            if ($actorInJail) {
                // Already in jail, extend time and send to super max
                $newJailTime = $this->getRemainingTime($actor) + 90;
                $this->sendToSuperMax($actor, $newJailTime);
            } else {
                // Send to jail for 90 seconds
                $this->sendToJail($actor, 90);
            }

            return [
                'success' => false,
                'message' => "You failed to break {$targetName} out of jail! You've been caught!",
                'jail_time' => 90,
            ];
        }
    }

    /**
     * Bail out of jail (pay to get out early)
     */
    public function bailOut(User $player): array
    {
        if (!$this->isInJail($player)) {
            return [
                'success' => false,
                'message' => "You are not in jail.",
            ];
        }

        // Can't bail out of super max
        if ($this->isInSuperMax($player)) {
            return [
                'success' => false,
                'message' => "You cannot bail out of super max. You must serve your time.",
            ];
        }

        $remainingTime = $this->getRemainingTime($player);
        
        // Calculate bail cost: $100 per second remaining
        $bailCost = $remainingTime * 100;

        if ($player->cash < $bailCost) {
            return [
                'success' => false,
                'message' => "You need $" . number_format($bailCost) . " to bail out. You only have $" . number_format($player->cash) . ".",
                'bail_cost' => $bailCost,
            ];
        }

        // Pay bail and release
        $player->cash -= $bailCost;
        $this->release($player);
        $player->save();

        return [
            'success' => true,
            'message' => "You paid $" . number_format($bailCost) . " bail and have been released from jail.",
            'bail_cost' => $bailCost,
        ];
    }
}
