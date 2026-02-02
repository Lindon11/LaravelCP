<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{
    /**
     * Get achievements data
     */
    public function index(Request $request, AchievementService $achievementService)
    {
        $user = $request->user();
        $achievements = $achievementService->getUserAchievements($user);

        // Group achievements by type
        $grouped = $achievements->groupBy('type');

        // Count earned vs total
        $stats = [
            'total' => $achievements->count(),
            'earned' => $achievements->where('is_earned', true)->count(),
            'percentage' => $achievements->count() > 0
                ? round(($achievements->where('is_earned', true)->count() / $achievements->count()) * 100)
                : 0
        ];

        return response()->json([
            'data' => $achievements->values()->all(), // Flat array for compatibility
            'achievements' => $grouped,
            'stats' => $stats,
        ]);
    }
}
