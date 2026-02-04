<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Http\Controllers\Controller;
use App\Core\Models\User;
use App\Plugins\Crimes\Models\CrimeAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardStatsController extends Controller
{
    public function index()
    {
        $days = request()->get('days', 7);
        $cacheKey = "dashboard_stats_{$days}";

        return response()->json(Cache::remember($cacheKey, 300, function () use ($days) {
            $startDate = Carbon::now()->subDays($days);
            $endDate = Carbon::now();
            $previousStartDate = Carbon::now()->subDays($days * 2);
            $previousEndDate = $startDate->copy();

            return [
                // Overview stats
                'totalUsers' => $this->getTotalUsers(),
                'newUsers' => $this->getNewUsers($startDate, $endDate, $previousStartDate, $previousEndDate),
                'activeUsers' => $this->getActiveUsers(),
                'activePercentage' => $this->getActivePercentage(),
                'crimesToday' => $this->getCrimesToday(),
                'crimesGrowth' => $this->getCrimesGrowth(),
                'totalMoney' => $this->getTotalMoney(),

                // Charts
                'activityChart' => $this->getActivityChart($days),
                'crimeChart' => $this->getCrimeDistribution(),

                // Economy
                'economy' => $this->getEconomyStats(),

                // Retention cohorts
                'retention' => $this->getRetentionData(),

                // Hourly activity heatmap
                'hourlyActivity' => $this->getHourlyActivity(),

                // Top activities
                'topActivities' => $this->getTopActivities($startDate, $endDate),

                // Level distribution
                'levelDistribution' => $this->getLevelDistribution(),

                // Game systems
                'employment' => $this->getEmploymentStats(),
                'education' => $this->getEducationStats(),
                'stocks' => $this->getStockStats(),
                'casino' => $this->getCasinoStats(),
            ];
        }));
    }

    private function getTotalUsers(): int
    {
        return User::count();
    }

    private function getNewUsers($startDate, $endDate, $previousStartDate, $previousEndDate): array
    {
        $current = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $previous = User::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

        return [
            'count' => $current,
            'change' => $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0
        ];
    }

    private function getActiveUsers(): int
    {
        return User::where('last_login_at', '>=', Carbon::now()->subDay())->count();
    }

    private function getActivePercentage(): float
    {
        $total = User::count();
        $active = User::where('last_login_at', '>=', Carbon::now()->subDay())->count();
        return $total > 0 ? round(($active / $total) * 100, 1) : 0;
    }

    private function getCrimesToday(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('crime_attempts')) {
            return 0;
        }
        return CrimeAttempt::whereDate('created_at', today())->count();
    }

    private function getCrimesGrowth(): int
    {
        if (!DB::getSchemaBuilder()->hasTable('crime_attempts')) {
            return 0;
        }

        $crimesToday = CrimeAttempt::whereDate('created_at', today())->count();
        $crimesYesterday = CrimeAttempt::whereDate('created_at', today()->subDay())->count();

        return $crimesYesterday > 0
            ? round((($crimesToday - $crimesYesterday) / $crimesYesterday) * 100)
            : 0;
    }

    private function getTotalMoney(): int
    {
        return User::sum('cash') + User::sum('bank');
    }

    private function getActivityChart(int $days): array
    {
        $labels = [];
        $activeUsersData = [];
        $newSignupsData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $days <= 7 ? $date->format('D') : $date->format('M d');
            $activeUsersData[] = User::whereDate('last_login_at', $date->toDateString())->count();
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

    private function getCrimeDistribution(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('crime_attempts')) {
            return ['labels' => ['No data'], 'data' => [0]];
        }

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

        if (empty($labels)) {
            $labels = ['Petty Theft', 'Grand Theft', 'Assault', 'Drug Deal', 'Robbery', 'Other'];
            $data = [0, 0, 0, 0, 0, 0];
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getEconomyStats(): array
    {
        $totalCash = User::sum('cash') ?? 0;
        $totalBank = User::sum('bank') ?? 0;

        return [
            'total_cash' => $totalCash,
            'total_bank' => $totalBank,
            'total' => $totalCash + $totalBank,
        ];
    }

    private function getRetentionData(): array
    {
        $cohorts = [];

        for ($week = 0; $week < 4; $week++) {
            $cohortStart = Carbon::now()->subWeeks($week + 1)->startOfWeek();
            $cohortEnd = $cohortStart->copy()->endOfWeek();

            $cohortUsers = DB::table('users')
                ->whereBetween('created_at', [$cohortStart, $cohortEnd])
                ->pluck('id');

            if ($cohortUsers->isEmpty()) {
                continue;
            }

            $total = $cohortUsers->count();
            $day1Active = $this->getReturnedUsers($cohortUsers, $cohortStart, 1);
            $day7Active = $this->getReturnedUsers($cohortUsers, $cohortStart, 7);

            $cohorts[] = [
                'week' => $week === 0 ? 'This Week' : ($week === 1 ? 'Last Week' : "{$week} Weeks Ago"),
                'users' => $total,
                'day1' => round(($day1Active / $total) * 100),
                'day7' => round(($day7Active / $total) * 100),
            ];
        }

        return $cohorts;
    }

    private function getReturnedUsers($userIds, $cohortStart, $days): int
    {
        $targetDate = $cohortStart->copy()->addDays($days);

        if ($targetDate->isFuture()) {
            return 0;
        }

        return DB::table('users')
            ->whereIn('id', $userIds)
            ->whereDate('last_login_at', '>=', $targetDate)
            ->count();
    }

    private function getHourlyActivity(): array
    {
        $hourlyData = [];

        if (DB::getSchemaBuilder()->hasTable('activity_log')) {
            $activities = DB::table('activity_log')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->pluck('count', 'hour')
                ->toArray();

            $maxActivity = max($activities ?: [1]);

            for ($hour = 0; $hour < 24; $hour++) {
                $count = $activities[$hour] ?? 0;
                $hourlyData[] = [
                    'hour' => $hour,
                    'value' => $maxActivity > 0 ? round(($count / $maxActivity) * 100) : 0,
                ];
            }
        } else {
            // Generate placeholder data
            for ($hour = 0; $hour < 24; $hour++) {
                $hourlyData[] = ['hour' => $hour, 'value' => 0];
            }
        }

        return $hourlyData;
    }

    private function getTopActivities($startDate, $endDate): array
    {
        if (!DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }

        $activities = DB::table('activity_log')
            ->select('log_name', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('log_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $maxCount = $activities->max('count') ?? 1;

        return $activities->map(function ($activity) use ($maxCount) {
            return [
                'name' => ucfirst(str_replace('_', ' ', $activity->log_name)),
                'count' => $activity->count,
                'percentage' => round(($activity->count / $maxCount) * 100),
            ];
        })->toArray();
    }

    private function getLevelDistribution(): array
    {
        if (!DB::getSchemaBuilder()->hasColumn('users', 'level')) {
            return [];
        }

        $total = User::count();
        if ($total === 0) return [];

        $ranges = [
            ['min' => 1, 'max' => 10, 'label' => '1-10'],
            ['min' => 11, 'max' => 25, 'label' => '11-25'],
            ['min' => 26, 'max' => 50, 'label' => '26-50'],
            ['min' => 51, 'max' => 75, 'label' => '51-75'],
            ['min' => 76, 'max' => 9999, 'label' => '76+'],
        ];

        return array_map(function ($range) use ($total) {
            $count = User::whereBetween('level', [$range['min'], $range['max']])->count();
            return [
                'range' => $range['label'],
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }, $ranges);
    }

    private function getEmploymentStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('companies')) {
            return ['total_companies' => 0, 'employed_users' => 0];
        }

        return [
            'total_companies' => DB::table('companies')->count(),
            'employed_users' => DB::table('player_employment')->where('is_active', true)->count(),
        ];
    }

    private function getEducationStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('education_courses')) {
            return ['total_courses' => 0, 'active_enrollments' => 0];
        }

        return [
            'total_courses' => DB::table('education_courses')->count(),
            'active_enrollments' => DB::table('user_education')->where('status', 'in_progress')->count(),
        ];
    }

    private function getStockStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('stocks')) {
            return ['total_stocks' => 0, 'investors' => 0];
        }

        return [
            'total_stocks' => DB::table('stocks')->count(),
            'investors' => DB::table('user_stocks')->distinct('user_id')->count(),
        ];
    }

    private function getCasinoStats(): array
    {
        if (!DB::getSchemaBuilder()->hasTable('casino_games')) {
            return ['total_games' => 0, 'bets_today' => 0];
        }

        return [
            'total_games' => DB::table('casino_games')->count(),
            'bets_today' => DB::table('casino_bets')->whereDate('played_at', today())->count(),
        ];
    }
}
