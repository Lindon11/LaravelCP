<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlayerStatsService;
use Illuminate\Http\Request;

class PlayerStatsController extends Controller
{
    protected PlayerStatsService $statsService;

    public function __construct(PlayerStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    /**
     * Get current player's statistics
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = $this->statsService->getPlayerStats($user);
        $leaderboard = $this->statsService->getLeaderboardPosition($user);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'leaderboard_position' => $leaderboard,
        ]);
    }

    /**
     * Get specific player's public statistics
     */
    public function show(Request $request, int $userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        // Only show public stats for other players
        $stats = $this->statsService->getPlayerStats($user);
        $leaderboard = $this->statsService->getLeaderboardPosition($user);

        // Remove sensitive information
        unset($stats['economy']['current_cash']);
        unset($stats['economy']['bank_balance']);

        return response()->json([
            'success' => true,
            'player' => [
                'id' => $user->id,
                'username' => $user->username,
                'level' => $user->level,
                'rank' => $user->rank,
            ],
            'stats' => $stats,
            'leaderboard_position' => $leaderboard,
        ]);
    }

    /**
     * Refresh stats cache
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        $this->statsService->clearCache($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Statistics refreshed successfully',
        ]);
    }
}
