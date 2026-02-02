<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Crimes\CrimesModule;
use App\Models\Crime;
use Illuminate\Http\Request;

class CrimesController extends Controller
{
    protected CrimesModule $module;
    
    public function __construct()
    {
        $this->module = new CrimesModule();
    }
    
    /**
     * Get available crimes
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $crimes = $this->module->getAvailableCrimes($user);
        
        $timer = $user->getTimer('crime');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'crimes' => $crimes,
            'cooldown' => $cooldown,
            'player' => $user->load('currentRank', 'location'),
        ]);
    }
    
    /**
     * Attempt a crime
     */
    public function attempt(Request $request)
    {
        $request->validate([
            'crime_id' => 'required|exists:crimes,id'
        ]);
        
        $user = $request->user();
        $crime = Crime::findOrFail($request->crime_id);
        
        $result = $this->module->attemptCrime($user, $crime);
        
        // Log activity
        if ($result['success'] && isset($result['crime_success'])) {
            app(\App\Services\ActivityLogService::class)->logCrime(
                $user,
                $crime,
                $result['crime_success'],
                $result['cash_gained'] ?? 0,
                $result['respect_gained'] ?? 0
            );
        }
        
        // Refresh user data
        $user->refresh();
        
        $timer = $user->getTimer('crime');
        $cooldown = $timer ? max(0, now()->diffInSeconds($timer->expires_at, false)) : 0;
        
        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['message'] ?? 'Crime attempted',
            'player' => [
                'cash' => $user->cash,
                'energy' => $user->energy,
                'experience' => $user->experience,
                'level' => $user->level,
            ],
            'cooldown' => $cooldown,
        ]);
    }
    
    /**
     * Get crime statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $stats = $this->module->getStats($user);
        
        return response()->json($stats);
    }
}
