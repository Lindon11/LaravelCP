<?php

namespace App\Plugins\Missions;

use App\Plugins\Plugin;

/**
 * Missions Module
 *
 * Handles mission system for player progression
 * Players can start missions and complete objectives for rewards
 */
class MissionsModule extends Plugin
{
    protected string $name = 'Missions';

    public function construct(): void
    {
        $this->config = [
            'max_active_missions' => 3,
            'cooldown' => 3600, // 1 hour
            'difficulty_multiplier' => [
                'easy' => 1.0,
                'medium' => 1.5,
                'hard' => 2.0,
                'extreme' => 3.0,
            ],
        ];
    }

    /**
     * Get available missions for player
     */
    public function getAvailableMissions($player): array
    {
        $missions = \App\Plugins\Missions\Models\Mission::where('enabled', true)
            ->where('min_level', '<=', $player->level)
            ->get();

        return $this->applyModuleHook('alterAvailableMissions', [
            'missions' => $missions,
            'player' => $player,
        ]);
    }

    /**
     * Get player's active missions
     */
    public function getActiveMissions($player): array
    {
        $active = \App\Plugins\Missions\Models\PlayerMission::where('user_id', $player->id)
            ->where('completed', false)
            ->with('mission')
            ->get();

        return $this->applyModuleHook('alterActiveMissions', [
            'missions' => $active,
            'player' => $player,
        ]);
    }

    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'total_missions' => \App\Plugins\Missions\Models\Mission::count(),
                'active' => 0,
                'completed' => 0,
            ];
        }

        return [
            'total_missions' => \App\Plugins\Missions\Models\Mission::count(),
            'active' => \App\Plugins\Missions\Models\PlayerMission::where('user_id', $user->id)->where('completed', false)->count(),
            'completed' => \App\Plugins\Missions\Models\PlayerMission::where('user_id', $user->id)->where('completed', true)->count(),
        ];
    }
}
