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

        // Add flattened stats for frontend compatibility
        $flatStats = [
            'crimes_committed' => $stats['crimes']['total_attempts'] ?? 0,
            'battles_won' => $stats['combat']['wins'] ?? 0,
            'battles_lost' => $stats['combat']['losses'] ?? 0,
            'win_rate' => $stats['combat']['win_rate'] ?? 0,
            'total_kills' => 0, // Could be added to combat stats later
            'jail_time' => 0, // Could be added to crimes stats later
            'times_jailed' => $stats['crimes']['times_jailed'] ?? 0,
            'bounties_claimed' => $stats['social']['bounties_claimed'] ?? 0,
            'bounties_placed' => $stats['social']['bounties_placed'] ?? 0,
        ];

        return response()->json(array_merge($flatStats, $stats)); // Return stats directly without wrapper
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
