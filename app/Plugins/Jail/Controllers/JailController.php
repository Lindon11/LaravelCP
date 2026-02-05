<?php

namespace App\Plugins\Jail\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Plugins\Jail\Services\JailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $player = Auth::user();
        
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

        return response()->json([
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
        $actor = Auth::user();

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
        $player = Auth::user();

        $result = $this->jailService->bailOut($player);

        if ($result['success']) {
            return redirect()->route('jail.index')->with('success', $result['message']);
        } else {
            return redirect()->route('jail.index')->with('error', $result['message']);
        }
    }
}
