<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CombatService;
use App\Models\CombatLocation;
use App\Models\CombatArea;
use App\Models\CombatFight;
use Illuminate\Http\Request;

class CombatController extends Controller
{
    public function __construct(
        private CombatService $combatService
    ) {}

    /**
     * Get combat locations for NPC battles
     */
    public function locations(Request $request)
    {
        $player = $request->user();

        $locations = CombatLocation::where('active', true)
            ->where('min_level', '<=', $player->level)
            ->orderBy('order')
            ->with(['areas' => function ($query) use ($player) {
                $query->where('active', true)
                    ->where('min_level', '<=', $player->level);
            }])
            ->get();

        return response()->json([
            'locations' => $locations,
            'player' => [
                'id' => $player->id,
                'username' => $player->username,
                'level' => $player->level,
                'health' => $player->health,
                'max_health' => $player->max_health,
                'energy' => $player->energy ?? 100,
            ],
        ]);
    }

    /**
     * Start hunting in an area (spawn NPC)
     */
    public function hunt(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:combat_locations,id',
            'area_id' => 'required|exists:combat_areas,id',
        ]);

        try {
            $player = $request->user();
            $result = $this->combatService->startNPCFight($player, $request->area_id);

            return response()->json([
                'success' => true,
                'enemy' => $result['enemy'],
                'fight_id' => $result['fight']->id,
                'weapons' => $result['weapons'],
                'equipment' => $result['equipment'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Attack NPC with selected weapon
     */
    public function attackNPC(Request $request)
    {
        $request->validate([
            'fight_id' => 'required|exists:combat_fights,id',
            'weapon_id' => 'nullable|exists:items,id',
        ]);

        try {
            $player = $request->user();
            $result = $this->combatService->attackNPC($player, $request->fight_id, $request->weapon_id);

            return response()->json([
                'success' => true,
                'result' => $result['result'], // 'hit', 'miss', 'critical'
                'message' => $result['message'],
                'enemy' => $result['enemy'],
                'player' => $result['player'],
                'ended' => $result['ended'],
                'end_message' => $result['end_message'] ?? null,
                'rewards' => $result['rewards'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Auto attack (use best weapon automatically)
     */
    public function autoAttackNPC(Request $request)
    {
        $request->validate([
            'fight_id' => 'required|exists:combat_fights,id',
        ]);

        try {
            $player = $request->user();
            $result = $this->combatService->autoAttackNPC($player, $request->fight_id);

            return response()->json([
                'success' => true,
                'logs' => $result['logs'],
                'enemy' => $result['enemy'],
                'player' => $result['player'],
                'ended' => $result['ended'],
                'end_message' => $result['end_message'] ?? null,
                'rewards' => $result['rewards'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get PvP combat interface data (old system)
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $availableTargets = $this->combatService->getAvailableTargets($player);
        $combatHistory = $this->combatService->getCombatHistory($player, 15);

        return response()->json([
            'availableTargets' => $availableTargets,
            'combatHistory' => $combatHistory,
            'player' => $player,
        ]);
    }

    /**
     * Attack a player (old PvP system)
     */
    public function attack(Request $request)
    {
        $request->validate([
            'defender_id' => 'required|exists:users,id',
        ]);

        try {
            $player = $request->user();
            $result = $this->combatService->attackPlayer($player, $request->defender_id);

            return response()->json([
                'success' => true,
                'killed' => $result['killed'] ?? false,
                'damage' => $result['damage'] ?? 0,
                'cash_stolen' => $result['cash_stolen'] ?? 0,
                'respect_gained' => $result['respect_gained'] ?? 0,
                'message' => $result['message'] ?? 'Attack successful!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
