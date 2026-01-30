<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Gym\GymModule;
use Illuminate\Http\Request;

class GymController extends Controller
{
    protected GymModule $module;
    
    public function __construct()
    {
        $this->module = new GymModule();
    }
    
    public function index(Request $request)
    {
        $user = $request->user();
        $activities = $this->module->getAvailableActivities($user);
        
        $timer = $user->getTimer('gym');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'activities' => $activities,
            'cooldown' => $cooldown,
        ]);
    }
    
    public function train(Request $request)
    {
        $request->validate([
            'activity_id' => 'required|exists:gym_activities,id'
        ]);
        
        $user = $request->user();
        $result = $this->module->train($user, $request->activity_id);
        
        $user->refresh();
        $timer = $user->getTimer('gym');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Training completed',
            'player' => [
                'strength' => $user->strength,
                'defense' => $user->defense,
                'speed' => $user->speed,
                'energy' => $user->energy,
                'experience' => $user->experience,
            ],
            'cooldown' => $cooldown,
        ]);
    }
}
