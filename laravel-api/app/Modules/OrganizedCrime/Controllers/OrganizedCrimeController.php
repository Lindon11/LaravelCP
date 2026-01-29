<?php

namespace App\Modules\OrganizedCrime\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Gang;
use App\Models\OrganizedCrime;
use App\Models\User;
use App\Services\OrganizedCrimeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizedCrimeController extends Controller
{
    public function __construct(
        protected OrganizedCrimeService $organizedCrimeService
    ) {}

    public function index(Request $request)
    {
        $player = $request->user();
        $crimes = $this->organizedCrimeService->getAvailableCrimes();
        
        $gang = $player && $player->gang_id ? Gang::with('members')->find($player->gang_id) : null;
        $history = $gang ? $this->organizedCrimeService->getGangHistory($gang) : [];

        return Inertia::render('Modules/OrganizedCrime/Index', [
            'player' => $player,
            'crimes' => $crimes,
            'gang' => $gang,
            'history' => $history,
        ]);
    }

    public function attempt(Request $request, OrganizedCrime $crime)
    {
        $request->validate([
            'participants' => 'required|array|min:' . $crime->required_members,
            'participants.*' => 'exists:players,id',
        ]);

        $player = User::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $result = $this->organizedCrimeService->attemptOrganizedCrime($player, $crime, $request->participants);
            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
