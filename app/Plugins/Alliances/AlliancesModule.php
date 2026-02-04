<?php

namespace App\Plugins\Alliances;

use App\Plugins\Plugin;
use App\Plugins\Alliances\Models\Alliance;
use App\Plugins\Alliances\Models\Territory;
use App\Plugins\Alliances\Models\AllianceWar;

/**
 * Alliances Module
 *
 * Gang alliances, territory control, and alliance warfare
 */
class AlliancesModule extends Plugin
{
    protected string $name = 'Alliances';

    public function construct(): void
    {
        $this->config = [
            'max_gangs_per_alliance' => 5,
            'min_gang_level_to_create' => 5,
            'create_cost' => 100000,
            'war_declaration_cost' => 50000,
            'territory_capture_time' => 3600, // 1 hour
            'war_duration' => 7 * 24 * 3600, // 7 days
        ];
    }

    /**
     * Get all alliances
     */
    public function getAlliances(): array
    {
        return Alliance::with(['gangs:id,name,level', 'leader:id,name'])
            ->withCount('gangs')
            ->orderBy('power', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get alliance details
     */
    public function getAlliance(int $id): ?array
    {
        $alliance = Alliance::with(['gangs', 'territories', 'wars'])
            ->find($id);

        return $alliance ? $alliance->toArray() : null;
    }

    /**
     * Get all territories
     */
    public function getTerritories(): array
    {
        return Territory::with(['owner:id,name', 'alliance:id,name'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Get active wars
     */
    public function getActiveWars(): array
    {
        return AllianceWar::where('status', 'active')
            ->with(['attacker:id,name', 'defender:id,name'])
            ->get()
            ->toArray();
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        return [
            'total_alliances' => Alliance::count(),
            'total_territories' => Territory::count(),
            'active_wars' => AllianceWar::where('status', 'active')->count(),
            'controlled_territories' => Territory::whereNotNull('alliance_id')->count(),
        ];
    }
}
