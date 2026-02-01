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
        $trainingInfo = $this->module->getTrainingInfo();
        
        $timer = $user->getTimer('gym');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'trainingInfo' => $trainingInfo,
            'attributes' => ['strength', 'defense', 'speed', 'stamina'],
            'player' => [
                'strength' => $user->strength,
                'defense' => $user->defense,
                'speed' => $user->speed,
                'energy' => $user->energy,
            ],
            'cooldown' => $cooldown,
        ]);
    }
    
    public function train(Request $request)
    {
        $request->validate([
            'attribute' => 'required|string|in:strength,defense,speed,stamina',
            'times' => 'required|integer|min:1|max:100'
        ]);
        
        $user = $request->user();
        $result = $this->module->train($user, $request->attribute, $request->times);
        
        // Log activity
        if ($result['success']) {
            app(\App\Services\ActivityLogService::class)->logGymTraining(
                $user,
                $request->attribute,
                $result['total_cost'] ?? 0
            );
        }
        
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
