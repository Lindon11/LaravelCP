<?php

namespace App\Plugins\Leaderboards\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Leaderboards\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardApiController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Get default leaderboard (level)
     */
    public function index(): JsonResponse
    {
        $leaderboard = $this->leaderboardService->getLeaderboard('level');

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get available leaderboard types
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->leaderboardService->getTypes(),
        ]);
    }

    /**
     * Get a specific leaderboard by type
     */
    public function show(string $type): JsonResponse
    {
        try {
            $leaderboard = $this->leaderboardService->getLeaderboard($type);

            return response()->json([
                'success' => true,
                'type' => $type,
                'data' => $leaderboard,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get the authenticated user's rank on a leaderboard
     */
    public function myRank(string $type): JsonResponse
    {
        $user = Auth::user();
        $rank = $this->leaderboardService->getPlayerRank($user, $type);

        return response()->json([
            'success' => true,
            'type' => $type,
            'data' => [
                'rank' => $rank,
                'username' => $user->username,
            ],
        ]);
    }
}
