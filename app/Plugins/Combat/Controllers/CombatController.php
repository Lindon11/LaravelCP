<?php

namespace App\Plugins\Combat\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Combat\Services\CombatService;
use App\Plugins\Combat\Services\NPCCombatService;
use App\Plugins\Combat\Models\CombatLocation;
use Illuminate\Http\Request;

class CombatController extends Controller
{
    public function __construct(
        private CombatService $combatService,
        private NPCCombatService $npcCombatService
    ) {}

    /**
     * Display combat interface
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $availableTargets = $this->combatService->getAvailableTargets($player);
        $combatHistory = $this->combatService->getCombatHistory($player, 15);

        return response()->json([
            'availableTargets' => $availableTargets,
            'combatHistory' => $combatHistory,
            'player' => $player->load(['equippedItems.item']),
        ]);
    }

    /**
     * Attack a player
     */
    public function attack(Request $request)
    {
        $request->validate([
            'defender_id' => 'required|exists:players,id',
        ]);

        try {
            $player = $request->user();
            $result = $this->combatService->attackPlayer($player, $request->defender_id);

            if ($result['killed']) {
                return back()->with('success', "You killed {$request->user()->username}! Gained {$result['respect_gained']} respect and \${$result['cash_stolen']}!");
            }

            return back()->with('success', "You dealt {$result['damage']} damage and stole \${$result['cash_stolen']}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get all combat locations with their areas for NPC hunting
     */
    public function locations(Request $request)
    {
        $user = $request->user();

        $locations = CombatLocation::with(['areas' => function ($query) {
                $query->where('active', true)->orderBy('difficulty');
            }])
            ->where('active', true)
            ->where('min_level', '<=', $user->level)
            ->orderBy('order')
            ->get()
            ->map(function ($location) use ($user) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'description' => $location->description,
                    'image' => $location->image,
                    'energy_cost' => $location->energy_cost,
                    'min_level' => $location->min_level,
                    'areas' => $location->areas->map(function ($area) {
                        return [
                            'id' => $area->id,
                            'name' => $area->name,
                            'description' => $area->description,
                            'difficulty' => $area->difficulty,
                            'min_level' => $area->min_level,
                        ];
                    }),
                ];
            });

        return response()->json([
            'locations' => $locations,
            'player' => $user->only(['id', 'username', 'level', 'health', 'max_health', 'energy', 'max_energy', 'cash']),
        ]);
    }

    /**
     * Start hunting (spawn NPC enemy)
     */
    public function hunt(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:combat_locations,id',
            'area_id' => 'required|exists:combat_areas,id',
        ]);

        try {
            $user = $request->user();
            $result = $this->npcCombatService->startHunt($user, $request->location_id, $request->area_id);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Attack NPC enemy
     */
    public function attackNPC(Request $request)
    {
        $request->validate([
            'fight_id' => 'required|exists:combat_fights,id',
            'weapon_id' => 'nullable|exists:items,id',
        ]);

        try {
            $user = $request->user();
            $result = $this->npcCombatService->attackNPC($user, $request->fight_id, $request->weapon_id);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Auto attack NPC enemy (3-5 attacks)
     */
    public function autoAttackNPC(Request $request)
    {
        $request->validate([
            'fight_id' => 'required|exists:combat_fights,id',
        ]);

        try {
            $user = $request->user();
            $result = $this->npcCombatService->autoAttackNPC($user, $request->fight_id);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
