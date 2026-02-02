<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gang;
use App\Models\OrganizedCrime;
use App\Services\OrganizedCrimeService;
use Illuminate\Http\Request;

class OrganizedCrimeController extends Controller
{
    public function __construct(
        protected OrganizedCrimeService $organizedCrimeService
    ) {}

    /**
     * Get organized crime data
     */
    public function index(Request $request)
    {
        $player = $request->user();
        $crimes = $this->organizedCrimeService->getAvailableCrimes();

        $gang = $player->gang_id ? Gang::with('members')->find($player->gang_id) : null;
        $history = $gang ? $this->organizedCrimeService->getGangHistory($gang) : [];

        return response()->json([
            'player' => $player,
            'crimes' => $crimes,
            'gang' => $gang,
            'history' => $history,
        ]);
    }

    /**
     * Attempt an organized crime
     */
    public function attempt(Request $request, OrganizedCrime $crime)
    {
        $request->validate([
            'participants' => 'required|array|min:' . $crime->required_members,
            'participants.*' => 'exists:users,id',
        ]);

        $player = $request->user();

        try {
            $result = $this->organizedCrimeService->attemptOrganizedCrime($player, $crime, $request->participants);
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'rewards' => $result['rewards'] ?? null,
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
