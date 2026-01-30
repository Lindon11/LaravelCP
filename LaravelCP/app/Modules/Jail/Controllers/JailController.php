<?php

namespace App\Modules\Jail\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JailService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JailController extends Controller
{
    protected $jailService;

    public function __construct(JailService $jailService)
    {
        $this->jailService = $jailService;
    }

    /**
     * Display the jail page with all jailed players
     */
    public function index()
    {
        $player = auth()->user();
        
        if (!$player) {
            return redirect()->route('dashboard')
                ->with('error', 'Player profile not found. Please contact support.');
        }
        
        $jailedPlayers = $this->jailService->getJailedPlayers($player);

        $playerStatus = null;
        if ($this->jailService->isInJail($player)) {
            $playerStatus = [
                'in_jail' => true,
                'in_super_max' => $this->jailService->isInSuperMax($player),
                'time_remaining' => $this->jailService->getRemainingTime($player),
                'jail_until' => $player->jail_until,
            ];
        }

        return Inertia::render('Modules/Jail/Index', [
            'jailedPlayers' => $jailedPlayers,
            'player' => $player,
            'playerStatus' => $playerStatus,
        ]);
    }

    /**
     * Attempt to bust a player out of jail
     */
    public function bustOut(Request $request, User $target)
    {
        $actor = auth()->user();

        $result = $this->jailService->attemptBustOut($actor, $target);

        if ($result['success']) {
            return redirect()->route('jail.index')->with('success', $result['message']);
        } else {
            return redirect()->route('jail.index')->with('error', $result['message']);
        }
    }

    /**
     * Pay bail to get out of jail
     */
    public function bailOut()
    {
        $player = auth()->user();

        $result = $this->jailService->bailOut($player);

        if ($result['success']) {
            return redirect()->route('jail.index')->with('success', $result['message']);
        } else {
            return redirect()->route('jail.index')->with('error', $result['message']);
        }
    }
}
