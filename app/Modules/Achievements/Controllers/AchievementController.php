<?php

namespace App\Modules\Achievements\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Inertia\Inertia;

class AchievementController extends Controller
{
    public function index(AchievementService $achievementService)
    {
        $user = auth()->user();
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
        
        return Inertia::render('Modules/Achievements/Index', [
            'achievements' => $grouped,
            'stats' => $stats
        ]);
    }
}
