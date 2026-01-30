<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CrimeAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show($id)
    {
        $player = User::findOrFail($id);
        
        // Get crime statistics
        $crimeStats = CrimeAttempt::where('user_id', $player->id)
            ->selectRaw('
                COUNT(*) as total_attempts,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed,
                SUM(cash_earned) as total_earnings,
                SUM(respect_earned) as total_respect
            ')
            ->first();
        
        $successRate = $crimeStats->total_attempts > 0 
            ? round(($crimeStats->successful / $crimeStats->total_attempts) * 100, 2)
            : 0;
        
        // Get recent crimes
        $recentCrimes = CrimeAttempt::where('user_id', $player->id)
            ->with('crime:id,name')
            ->orderBy('attempted_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'crime_name' => $attempt->crime->name,
                    'success' => $attempt->success,
                    'cash_earned' => $attempt->cash_earned,
                    'time_ago' => $attempt->attempted_at->diffForHumans(),
                ];
            });
        
        // Get rank title
        $rank = \DB::table('ranks')
            ->where('required_level', '<=', $player->level)
            ->orderBy('required_level', 'desc')
            ->first();
        
        return Inertia::render('Profile/PlayerProfile', [
            'player' => [
                'id' => $player->id,
                'username' => $player->username,
                'level' => $player->level,
                'experience' => $player->experience,
                'rank_title' => $rank->name ?? 'Thug',
                'respect' => $player->respect,
                'cash' => $player->cash,
                'bank' => $player->bank,
                'networth' => $player->cash + $player->bank,
                'health' => $player->health,
                'max_health' => $player->max_health,
                'energy' => $player->energy,
                'max_energy' => $player->max_energy,
                'bullets' => $player->bullets,
                'location' => $player->location,
                'created_at' => $player->created_at->toIso8601String(),
                'last_active' => $player->last_active ? $player->last_active->toIso8601String() : null,
            ],
            'stats' => [
                'total_attempts' => $crimeStats->total_attempts ?? 0,
                'successful' => $crimeStats->successful ?? 0,
                'failed' => $crimeStats->failed ?? 0,
                'success_rate' => $successRate,
                'total_earnings' => $crimeStats->total_earnings ?? 0,
                'total_respect_earned' => $crimeStats->total_respect ?? 0,
            ],
            'recent_crimes' => $recentCrimes,
            'is_own_profile' => auth()->id() === $player->id,
        ]);
    }
}
