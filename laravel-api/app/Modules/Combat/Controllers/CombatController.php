<?php

namespace App\Modules\Combat\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CombatService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CombatController extends Controller
{
    public function __construct(
        private CombatService $combatService
    ) {}

    /**
     * Display combat interface
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $availableTargets = $this->combatService->getAvailableTargets($player);
        $combatHistory = $this->combatService->getCombatHistory($player, 15);

        return Inertia::render('Modules/Combat/Index', [
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
}
