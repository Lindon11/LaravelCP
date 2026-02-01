<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\TravelService;
use Illuminate\Http\Request;

class TravelController extends Controller
{
    public function __construct(
        protected TravelService $travelService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $locations = $this->travelService->getAvailableLocations();
        $currentLocation = $player ? Location::find($player->location_id) : null;
        $playersHere = $currentLocation ? $this->travelService->getPlayersInLocation($currentLocation) : [];

        return response()->json([
            'locations' => $locations,
            'currentLocation' => $currentLocation,
            'playersHere' => $playersHere,
            'player' => [
                'location_id' => $player->location_id,
            ],
        ]);
    }

    public function travel(Request $request, Location $location)
    {
        $player = $request->user();

        try {
            $result = $this->travelService->travel($player, $location);
            $player->refresh();
            
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Traveled successfully',
                'player' => [
                    'location_id' => $player->location_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
