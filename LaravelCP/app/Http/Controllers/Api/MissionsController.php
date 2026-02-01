<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MissionService;
use Illuminate\Http\Request;

class MissionsController extends Controller
{
    public function __construct(
        private MissionService $missionService
    ) {}

    /**
     * Get missions data
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $missions = $this->missionService->getAvailableMissions($player);
        $stats = $this->missionService->getPlayerStats($player);

        return response()->json([
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

            return response()->json([
                'success' => true,
                'message' => "Mission started: {$playerMission->mission->name}!",
                'mission' => $playerMission,
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
