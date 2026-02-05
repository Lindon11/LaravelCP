<?php

namespace App\Modules\Achievements\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Achievements\Services\AchievementService;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index(AchievementService $achievementService)
    {
        /** @var \App\Core\Models\User $user */
        $user = Auth::user();
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
            'achievements' => $grouped,
            'stats' => $stats,
        ]);
    }
}
