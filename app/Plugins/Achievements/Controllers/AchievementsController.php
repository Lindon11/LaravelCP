<?php

namespace App\Plugins\Achievements\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Achievements\AchievementsModule;
use Illuminate\Http\Request;

class AchievementsController extends Controller
{
    protected AchievementsModule $module;
    
    public function __construct()
    {
        $this->module = new AchievementsModule();
    }
    
    /**
     * Get achievements data
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $achievements = $this->module->getUserAchievements($user);
        $grouped = $this->module->getGroupedAchievements($user);
        $stats = $this->module->getStats($user);

        return response()->json([
            'data' => $achievements->values()->all(),
            'achievements' => $grouped,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Get recent achievements
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        $recent = $this->module->getRecentlyEarned($user, 10);
        
        return response()->json([
            'success' => true,
            'data' => $recent,
        ]);
    }
}
