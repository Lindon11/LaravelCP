<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;

use App\Plugins\Crimes\Models\Crime;
use App\Plugins\Combat\Models\CombatLog;
use App\Plugins\Gang\Models\Gang;
use App\Core\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SystemController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'players' => [
                'total' => User::count(),
                'online' => User::where('updated_at', '>=', now()->subMinutes(15))->count(),
                'active_today' => User::where('updated_at', '>=', now()->startOfDay())->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],
            'activity' => [
                'crimes_today' => DB::table('crime_attempts')->whereDate('created_at', today())->count(),
                'combat_today' => CombatLog::whereDate('created_at', today())->count(),
                'gangs' => Gang::count(),
                'users' => User::count(),
            ],
            'economy' => [
                'total_cash' => User::sum('cash'),
                'total_bank' => User::sum('bank'),
                'average_level' => round(User::avg('level'), 1),
                'total_respect' => User::sum('respect'),
            ],
            'top_players' => User::orderBy('respect', 'desc')->take(5)->get(['id', 'name', 'level', 'respect']),
            'recent_signups' => User::with('user')->latest()->take(5)->get(),
        ];

        return Inertia::render('Admin/System/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function playerActivity()
    {
        $hourly = DB::table('players')
            ->select(DB::raw('HOUR(updated_at) as hour'), DB::raw('COUNT(*) as count'))
            ->where('updated_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $daily = DB::table('players')
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(DISTINCT id) as count'))
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'hourly' => $hourly,
            'daily' => $daily,
        ]);
    }

    public function serverHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'storage' => $this->checkStorageHealth(),
        ];

        return response()->json($health);
    }

    protected function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'healthy',
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function checkCacheHealth(): array
    {
        try {
            \Cache::put('health_check', true, 60);
            $result = \Cache::get('health_check');

            return [
                'status' => $result ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function checkStorageHealth(): array
    {
        try {
            $path = storage_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percentage = round(($used / $total) * 100, 2);

            return [
                'status' => $percentage < 90 ? 'healthy' : 'warning',
                'used_percentage' => $percentage,
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}

