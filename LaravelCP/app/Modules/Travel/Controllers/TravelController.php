<?php

namespace App\Modules\Travel\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\TravelService;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Modules/Travel/Index', [
            'player' => $player,
            'locations' => $locations,
            'currentLocation' => $currentLocation,
            'playersHere' => $playersHere,
        ]);
    }

    public function travel(Request $request, Location $location)
    {
        $player = $request->user();

        try {
            $result = $this->travelService->travel($player, $location);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
