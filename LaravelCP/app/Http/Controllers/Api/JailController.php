<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JailService;
use Illuminate\Http\Request;

class JailController extends Controller
{
    public function __construct(
        protected JailService $jailService
    ) {}

    /**
     * Get jail information and jailed players
     */
    public function index(Request $request)
    {
        $player = $request->user();

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
        $actor = $request->user();

        try {
            $result = $this->jailService->attemptBustOut($actor, $target);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'player' => $actor->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Bail yourself out of jail
     */
    public function bailOut(Request $request)
    {
        $player = $request->user();

        try {
            $result = $this->jailService->bailOut($player);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
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
