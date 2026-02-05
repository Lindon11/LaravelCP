<?php

namespace App\Plugins\Racing\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Racing\Models\Race;
use App\Plugins\Racing\Services\RaceService;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    protected $raceService;

    public function __construct(RaceService $raceService)
    {
        $this->raceService = $raceService;
    }

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

            return redirect()->back()->with('success', 'Race created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

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

            return redirect()->back()->with('success', 'Joined race successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function leave(Request $request, Race $race)
    {
        try {
            $player = $request->user();
            $this->raceService->leaveRace($player, $race->id);

            return redirect()->back()->with('success', 'Left race (90% refunded)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function start(Request $request, Race $race)
    {
        try {
            // Only race creator or admin can start
            $player = $request->user();
            
            $results = $this->raceService->startRace($race->id);

            return redirect()->back()->with('success', 'Race finished! Check results below.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
