<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Services\RaceService;
use Illuminate\Http\Request;

class RacingController extends Controller
{
    public function __construct(
        protected RaceService $raceService
    ) {}

    /**
     * Get racing data
     */
    public function index(Request $request)
    {
        $player = $request->user();

        $availableRaces = $this->raceService->getAvailableRaces($player);
        $raceHistory = $this->raceService->getRaceHistory($player, 5);

        // Get player's vehicles
        $vehicles = $player->inventory()
            ->with('item')
            ->whereHas('item', function ($query) {
                $query->where('type', 'vehicle');
            })
            ->get();

        return response()->json([
            'availableRaces' => $availableRaces,
            'raceHistory' => $raceHistory,
            'vehicles' => $vehicles,
            'player' => $player,
        ]);
    }

    /**
     * Create a race
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'entry_fee' => 'required|integer|min:100|max:1000000',
            'min_participants' => 'integer|min:2|max:8',
            'max_participants' => 'integer|min:2|max:8',
        ]);

        try {
            $player = $request->user();
            $race = $this->raceService->createRace($player, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Race created successfully!',
                'race' => $race,
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Join a race
     */
    public function join(Request $request, Race $race)
    {
        $request->validate([
            'vehicle_id' => 'nullable|integer|exists:player_inventories,id',
            'bet_amount' => 'integer|min:0',
        ]);

        try {
            $player = $request->user();
            $this->raceService->joinRace(
                $player,
                $race->id,
                $request->vehicle_id,
                $request->bet_amount ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Joined race successfully!',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Leave a race
     */
    public function leave(Request $request, Race $race)
    {
        try {
            $player = $request->user();
            $this->raceService->leaveRace($player, $race->id);

            return response()->json([
                'success' => true,
                'message' => 'Left race (90% refunded)',
                'player' => $player->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Start a race
     */
    public function start(Request $request, Race $race)
    {
        try {
            $player = $request->user();
            $result = $this->raceService->startRace($player, $race->id);

            return response()->json([
                'success' => true,
                'message' => 'Race started!',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
