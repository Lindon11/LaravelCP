<?php

namespace App\Plugins\Missions\Services;
use App\Core\Models\User;
use App\Plugins\Missions\Models\Mission;
use App\Plugins\Missions\Models\PlayerMission;
use Exception;
class MissionService
{
    /**
     * Get available missions for player
     */
    public function getAvailableMissions(User $player)
    {
        $missions = Mission::with(['location', 'itemReward'])
            ->where('is_active', true)
            ->where('required_level', '<=', $player->level)
            ->where(function ($query) use ($player) {
                $query->whereNull('required_location_id')
                    ->orWhere('required_location_id', $player->location_id);
            })
            ->orderBy('order')
            ->orderBy('required_level')
            ->get();
        return $missions->map(function ($mission) use ($player) {
            $playerMission = PlayerMission::where('user_id', $player->id)
                ->where('mission_id', $mission->id)
                ->first();
            // Check if mission is available based on type
            $isAvailable = true;
            $canStart = true;
            if ($playerMission) {
                if ($mission->type === 'one_time' && $playerMission->isCompleted()) {
                    $isAvailable = false;
                }
                if ($mission->type === 'repeatable' && $playerMission->isCompleted()) {
                    if (!$playerMission->canRepeat()) {
                        $canStart = false;
                    }
                }

                if ($mission->type === 'daily' && $playerMission->completed_at) {
                    if (!$playerMission->completed_at->isToday() || $playerMission->canRepeat()) {
                        $canStart = true;
                    } else {
                        $canStart = false;
                    }
                }
            }
            return [
                'id' => $mission->id,
                'name' => $mission->name,
                'description' => $mission->description,
                'type' => $mission->type,
                'objective_type' => $mission->objective_type,
                'objective_count' => $mission->objective_count,
                'required_level' => $mission->required_level,
                'location' => $mission->location?->name,
                'rewards' => [
                    'cash' => $mission->cash_reward,
                    'respect' => $mission->respect_reward,
                    'experience' => $mission->experience_reward,
                    'item' => $mission->itemReward?->name,
                    'item_quantity' => $mission->item_reward_quantity,
                ],
                'progress' => $playerMission?->progress ?? 0,
                'status' => $playerMission?->status ?? 'available',
                'is_available' => $isAvailable,
                'can_start' => $canStart,
                'cooldown_hours' => $mission->cooldown_hours,
                'available_again_at' => $playerMission?->available_again_at,
            ];
        })->filter(function ($mission) {
            return $mission['is_available'];
        })->values();
    }

    /**
     * Start a mission
     */
    public function startMission(User $player, int $missionId)
    {
        $mission = Mission::findOrFail($missionId);
        if (!$mission->isAvailableFor($player)) {
            throw new Exception('This mission is not available to you.');
        }
        $playerMission = PlayerMission::firstOrNew([
            'user_id' => $player->id,
            'mission_id' => $mission->id,
        ]);
        // Check if already completed (one-time missions)
        if ($mission->type === 'one_time' && $playerMission->exists && $playerMission->isCompleted()) {
            throw new Exception('You have already completed this mission.');
        }

        // Check if on cooldown (repeatable missions)
        if ($mission->type === 'repeatable' && $playerMission->exists && !$playerMission->canRepeat()) {
            $timeLeft = $playerMission->available_again_at->diffForHumans();
            throw new Exception("This mission will be available again {$timeLeft}.");
        }

        // Reset or create mission progress
        $playerMission->fill([
            'status' => 'in_progress',
            'progress' => 0,
            'started_at' => now(),
            'completed_at' => null,
            'available_again_at' => null,
        ]);

        $playerMission->save();
        return $playerMission;
    }

    /**
     * Update mission progress
     */
    public function updateProgress(User $player, string $objectiveType, ?array $data = null)
    {
        $activeMissions = PlayerMission::with('mission')
            ->where('user_id', $player->id)
            ->where('status', 'in_progress')
            ->whereHas('mission', function ($query) use ($objectiveType) {
                $query->where('objective_type', $objectiveType);
            })
            ->get();

        foreach ($activeMissions as $playerMission) {
            $mission = $playerMission->mission;
            // Check if objective data matches (if specified)
            if ($mission->objective_data && $data) {
                $matches = true;
                foreach ($mission->objective_data as $key => $value) {
                    if (!isset($data[$key]) || $data[$key] != $value) {
                        $matches = false;
                        break;
                    }
                }

                if (!$matches) {
                    continue;
                }
            }

            // Increment progress
            $playerMission->progress++;
            // Check if mission is completed
            if ($playerMission->progress >= $mission->objective_count) {
                $this->completeMission($player, $playerMission);
            } else {
                $playerMission->save();
            }
        }
    }

    /**
     * Complete a mission and give rewards
     */
    private function completeMission(User $player, PlayerMission $playerMission)
    {
        $mission = $playerMission->mission;
        // Give rewards
        if ($mission->cash_reward > 0) {
            $player->cash += $mission->cash_reward;
        }

        if ($mission->respect_reward > 0) {
            $player->respect += $mission->respect_reward;
        }

        if ($mission->experience_reward > 0) {
            $player->experience += $mission->experience_reward;
        }
        // Give item reward
        if ($mission->item_reward_id) {
            $inventory = \App\Plugins\Inventory\Models\PlayerInventory::firstOrCreate([
                'user_id' => $player->id,
                'item_id' => $mission->item_reward_id,
            ], [
                'quantity' => 0,
            ]);
            $inventory->quantity += $mission->item_reward_quantity;
            $inventory->save();
        }

        $player->save();

        // Update mission status
        $playerMission->status = 'completed';
        $playerMission->completed_at = now();

        // Set cooldown for repeatable missions
        if ($mission->type === 'repeatable' && $mission->cooldown_hours > 0) {
            $playerMission->available_again_at = now()->addHours($mission->cooldown_hours);
        }

        // Daily missions reset next day
        if ($mission->type === 'daily') {
            $playerMission->available_again_at = now()->addDay()->startOfDay();
        }

        $playerMission->save();

        return [
            'mission_name' => $mission->name,
            'rewards' => [
                'cash' => $mission->cash_reward,
                'respect' => $mission->respect_reward,
                'experience' => $mission->experience_reward,
                'item' => $mission->itemReward?->name,
                'item_quantity' => $mission->item_reward_quantity,
            ],
        ];
    }

    /**
     * Claim completed mission rewards manually (if not auto-claimed)
     */
    public function claimReward(User $player, int $playerMissionId)
    {
        $playerMission = PlayerMission::with('mission')
            ->where('id', $playerMissionId)
            ->firstOrFail();
        if (!$playerMission->isCompleted()) {
            throw new Exception('This mission is not completed yet.');
        }

        // Return mission info (rewards were already given on completion)
        return $playerMission;
    }

    /**
     * Get player's mission statistics
     */
    public function getPlayerStats(User $player)
    {
        $total = PlayerMission::where('user_id', $player->id)->count();
        $completed = PlayerMission::where('user_id', $player->id)
            ->where('status', 'completed')
            ->count();

        $inProgress = PlayerMission::where('user_id', $player->id)
            ->where('status', 'in_progress')
            ->count();

        return [
            'total_missions' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ];
    }
}
