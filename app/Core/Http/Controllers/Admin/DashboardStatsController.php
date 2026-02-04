<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Plugins\Crimes\Models\CrimeAttempt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardStatsController extends Controller
{
    public function index()
    {
        // Total users
        $totalUsers = User::count();
        
        // New users this week
        $newUsers = User::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        
        // Active users (last 24 hours)
        $activeUsers = User::where('last_active', '>=', Carbon::now()->subDay())->count();
        
        // Crimes today
        $crimesToday = CrimeAttempt::whereDate('created_at', today())->count();
        
        // Crimes growth (compared to yesterday)
        $crimesYesterday = CrimeAttempt::whereDate('created_at', today()->subDay())->count();
        $crimesGrowth = $crimesYesterday > 0 
            ? round((($crimesToday - $crimesYesterday) / $crimesYesterday) * 100) 
            : 0;
        
        // Total money in circulation
        $totalMoney = User::sum('cash') + User::sum('bank');
        
        // Activity data for last 7 days
        $activityData = $this->getActivityData();
        
        // Crime distribution
        $crimeDistribution = $this->getCrimeDistribution();
        
        // New systems stats
        $employmentStats = $this->getEmploymentStats();
        $educationStats = $this->getEducationStats();
        $stockStats = $this->getStockStats();
        $casinoStats = $this->getCasinoStats();
        
        return response()->json([
            'totalUsers' => $totalUsers,
            'newUsers' => $newUsers,
            'activeUsers' => $activeUsers,
            'crimesToday' => $crimesToday,
            'crimesGrowth' => $crimesGrowth,
            'totalMoney' => $totalMoney,
            'activityChart' => $activityData,
            'crimeChart' => $crimeDistribution,
            'employment' => $employmentStats,
            'education' => $educationStats,
            'stocks' => $stockStats,
            'casino' => $casinoStats,
        ]);
    }
    
    private function getActivityData()
    {
        $labels = [];
        $activeUsersData = [];
        $newSignupsData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D');
            
            // Active users on that day
            $activeUsersData[] = User::whereDate('last_active', $date->toDateString())->count();
            
            // New signups on that day
            $newSignupsData[] = User::whereDate('created_at', $date->toDateString())->count();
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Active Users',
                    'data' => $activeUsersData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)'
                ],
                [
                    'label' => 'New Signups',
                    'data' => $newSignupsData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ]
            ]
        ];
    }
    
    private function getCrimeDistribution()
    {
        $crimeTypes = DB::table('crime_attempts')
            ->join('crimes', 'crime_attempts.crime_id', '=', 'crimes.id')
            ->select('crimes.name', DB::raw('count(*) as total'))
            ->where('crime_attempts.created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('crimes.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();
        
        $labels = $crimeTypes->pluck('name')->toArray();
        $data = $crimeTypes->pluck('total')->toArray();
        
        // If no data, return default
        if (empty($labels)) {
            $labels = ['Petty Theft', 'Grand Theft', 'Assault', 'Drug Deal', 'Robbery', 'Other'];
            $data = [0, 0, 0, 0, 0, 0];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getEmploymentStats()
    {
        return [
            'total_companies' => DB::table('companies')->count(),
            'total_positions' => DB::table('employment_positions')->count(),
            'employed_users' => DB::table('player_employment')->where('is_active', true)->count(),
            'total_earnings_today' => DB::table('work_shifts')->whereDate('worked_at', today())->sum('earnings'),
        ];
    }

    private function getEducationStats()
    {
        return [
            'total_courses' => DB::table('education_courses')->count(),
            'active_enrollments' => DB::table('user_education')->where('status', 'in_progress')->count(),
            'completed_today' => DB::table('user_education')->where('status', 'completed')->whereDate('completed_at', today())->count(),
        ];
    }

    private function getStockStats()
    {
        $totalMarketCap = DB::table('stocks')->sum('market_cap');
        $transactionsToday = DB::table('stock_transactions')->whereDate('executed_at', today())->count();
        $volumeToday = DB::table('stock_transactions')->whereDate('executed_at', today())->sum('total_amount');
        
        return [
            'total_stocks' => DB::table('stocks')->count(),
            'total_market_cap' => $totalMarketCap,
            'transactions_today' => $transactionsToday,
            'volume_today' => $volumeToday,
            'investors' => DB::table('user_stocks')->distinct('user_id')->count(),
        ];
    }

    private function getCasinoStats()
    {
        $betsToday = DB::table('casino_bets')->whereDate('played_at', today())->count();
        $wageredToday = DB::table('casino_bets')->whereDate('played_at', today())->sum('bet_amount');
        $houseProfit = DB::table('casino_bets')->whereDate('played_at', today())->sum('profit_loss') * -1;
        
        return [
            'total_games' => DB::table('casino_games')->count(),
            'bets_today' => $betsToday,
            'wagered_today' => $wageredToday,
            'house_profit_today' => $houseProfit,
            'active_players' => DB::table('user_casino_stats')->where('total_bets', '>', 0)->count(),
        ];
    }
}
