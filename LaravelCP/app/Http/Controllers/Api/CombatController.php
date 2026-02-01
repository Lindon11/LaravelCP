<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CombatService;
use Illuminate\Http\Request;

class CombatController extends Controller
{
    public function __construct(
        private CombatService $combatService
    ) {}

    /**
     * Get combat interface data
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
     * Attack a player
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
