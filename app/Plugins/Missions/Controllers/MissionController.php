<?php

namespace App\Plugins\Missions\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Missions\Services\MissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionController extends Controller
{
    public function __construct(
        private MissionService $missionService
    ) {}

    /**
     * Display missions interface
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $missions = $this->missionService->getAvailableMissions($player);
        $stats = $this->missionService->getPlayerStats($player);

        return Inertia::render('Modules/Missions/Index', [
            'missions' => $missions,
            'stats' => $stats,
            'player' => $player,
        ]);
    }

    /**
     * Start a mission
     */
    public function start(Request $request)
    {
        $request->validate([
            'mission_id' => 'required|exists:missions,id',
        ]);

        try {
            $player = $request->user();
            $playerMission = $this->missionService->startMission($player, $request->mission_id);

            return back()->with('success', "Mission started: {$playerMission->mission->name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
