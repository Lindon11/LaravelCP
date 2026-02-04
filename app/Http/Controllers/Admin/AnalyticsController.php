<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get overall analytics stats
     */
    public function index(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days);
        $previousStartDate = Carbon::now()->subDays($days * 2);
        $previousEndDate = $startDate->copy();

        // Cache key based on date range
        $cacheKey = "analytics_stats_{$days}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $previousStartDate, $previousEndDate, $days) {
            return [
                'stats' => $this->getOverviewStats($startDate, $endDate, $previousStartDate, $previousEndDate),
                'economy' => $this->getEconomyStats(),
                'activity_chart' => $this->getActivityChart($days),
                'hourly_activity' => $this->getHourlyActivity(),
                'top_activities' => $this->getTopActivities($startDate, $endDate),
                'level_distribution' => $this->getLevelDistribution(),
                'retention' => $this->getRetentionData(),
            ];
        });

        return response()->json($data);
    }

    /**
     * Get overview statistics
     */
    protected function getOverviewStats($startDate, $endDate, $previousStartDate, $previousEndDate): array
    {
        $totalUsers = DB::table('users')->count();
        $previousTotalUsers = DB::table('users')
            ->where('created_at', '<', $startDate)
            ->count();
        
        $activeToday = DB::table('users')
            ->whereDate('last_login_at', Carbon::today())
            ->count();

        $newRegistrations = DB::table('users')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $previousRegistrations = DB::table('users')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();

        // Calculate session time from activity logs if available
        $avgSessionTime = $this->calculateAverageSessionTime();

        return [
            'total_users' => $totalUsers,
            'users_change' => $previousTotalUsers > 0 
                ? round((($totalUsers - $previousTotalUsers) / $previousTotalUsers) * 100, 1)
                : 0,
            'active_today' => $activeToday,
            'active_percentage' => $totalUsers > 0 
                ? round(($activeToday / $totalUsers) * 100, 1)
                : 0,
            'new_registrations' => $newRegistrations,
            'registrations_change' => $previousRegistrations > 0
                ? round((($newRegistrations - $previousRegistrations) / $previousRegistrations) * 100, 1)
                : 0,
            'avg_session_time' => $avgSessionTime,
        ];
    }

    /**
     * Get economy statistics
     */
    protected function getEconomyStats(): array
    {
        $totalCash = DB::table('users')->sum('cash') ?? 0;
        $totalBank = DB::table('users')->sum('bank') ?? 0;

        // These would need proper tracking tables in a real implementation
        $generatedToday = 0;
        $spentToday = 0;

        if (DB::getSchemaBuilder()->hasTable('transactions')) {
            $generatedToday = DB::table('transactions')
                ->whereDate('created_at', Carbon::today())
                ->where('type', 'income')
                ->sum('amount') ?? 0;

            $spentToday = DB::table('transactions')
                ->whereDate('created_at', Carbon::today())
                ->where('type', 'expense')
                ->sum('amount') ?? 0;
        }

        return [
            'total_cash' => $totalCash,
            'total_bank' => $totalBank,
            'generated_today' => $generatedToday,
            'spent_today' => $spentToday,
        ];
    }

    /**
     * Get activity chart data
     */
    protected function getActivityChart(int $days): array
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $activeUsers = DB::table('users')
                ->whereDate('last_login_at', $date)
                ->count();

            $data[] = [
                'label' => $date->format('M d'),
                'value' => $activeUsers,
            ];
        }

        // If showing weekly, group by day name
        if ($days <= 7) {
            return array_map(function ($item) {
                $item['label'] = Carbon::parse($item['label'])->format('D');
                return $item;
            }, $data);
        }

        return $data;
    }

    /**
     * Get hourly activity heatmap
     */
    protected function getHourlyActivity(): array
    {
        $hourlyData = [];

        // Get activity by hour from the last 7 days
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

        return $hourlyData;
    }

    /**
     * Get top activities
     */
    protected function getTopActivities($startDate, $endDate): array
    {
        if (!DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }

        $activities = DB::table('activity_log')
            ->select('log_name', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('log_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $maxCount = $activities->max('count') ?? 1;

        return $activities->map(function ($activity) use ($maxCount) {
            return [
                'name' => ucfirst(str_replace('_', ' ', $activity->log_name)),
                'count' => $activity->count,
                'percentage' => round(($activity->count / $maxCount) * 100),
                'icon' => $this->getActivityIcon($activity->log_name),
            ];
        })->toArray();
    }

    /**
     * Get level distribution
     */
    protected function getLevelDistribution(): array
    {
        if (!DB::getSchemaBuilder()->hasColumn('users', 'level')) {
            return [];
        }

        $total = DB::table('users')->count();
        if ($total === 0) return [];

        $ranges = [
            ['min' => 1, 'max' => 10, 'label' => 'Level 1-10', 'color' => 'bg-emerald-500'],
            ['min' => 11, 'max' => 25, 'label' => 'Level 11-25', 'color' => 'bg-blue-500'],
            ['min' => 26, 'max' => 50, 'label' => 'Level 26-50', 'color' => 'bg-violet-500'],
            ['min' => 51, 'max' => 75, 'label' => 'Level 51-75', 'color' => 'bg-amber-500'],
            ['min' => 76, 'max' => 9999, 'label' => 'Level 76+', 'color' => 'bg-red-500'],
        ];

        return array_map(function ($range) use ($total) {
            $count = DB::table('users')
                ->whereBetween('level', [$range['min'], $range['max']])
                ->count();

            return [
                'range' => $range['label'],
                'percentage' => round(($count / $total) * 100, 1),
                'color' => $range['color'],
            ];
        }, $ranges);
    }

    /**
     * Get retention cohort data
     */
    protected function getRetentionData(): array
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

            $day1Active = $this->getReturnedUsers($cohortUsers, $cohortStart, 1);
            $day7Active = $this->getReturnedUsers($cohortUsers, $cohortStart, 7);
            $day30Active = $this->getReturnedUsers($cohortUsers, $cohortStart, 30);

            $total = $cohortUsers->count();

            $cohorts[] = [
                'week' => $week === 0 ? 'This Week' : ($week === 1 ? 'Last Week' : "{$week} Weeks Ago"),
                'day1' => round(($day1Active / $total) * 100),
                'day7' => round(($day7Active / $total) * 100),
                'day30' => round(($day30Active / $total) * 100),
            ];
        }

        return $cohorts;
    }

    /**
     * Get number of users who returned after N days
     */
    protected function getReturnedUsers($userIds, $cohortStart, $days): int
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

    /**
     * Calculate average session time
     */
    protected function calculateAverageSessionTime(): int
    {
        // This would require proper session tracking
        // For now, return a placeholder
        return 24; // minutes
    }

    /**
     * Get icon for activity type
     */
    protected function getActivityIcon(string $type): string
    {
        $icons = [
            'crime' => '🔫',
            'combat' => '⚔️',
            'travel' => '✈️',
            'job' => '💼',
            'casino' => '🎰',
            'bank' => '🏦',
            'shop' => '🛒',
            'gym' => '💪',
            'education' => '📚',
            'gang' => '👥',
        ];

        foreach ($icons as $key => $icon) {
            if (str_contains(strtolower($type), $key)) {
                return $icon;
            }
        }

        return '📊';
    }

    /**
     * Get real-time active users count
     */
    public function realtime(): JsonResponse
    {
        $activeUsers = DB::table('users')
            ->where('last_login_at', '>=', Carbon::now()->subMinutes(5))
            ->count();

        $recentEvents = [];
        
        if (DB::getSchemaBuilder()->hasTable('activity_log')) {
            $recentEvents = DB::table('activity_log')
                ->join('users', 'activity_log.causer_id', '=', 'users.id')
                ->select('activity_log.*', 'users.username')
                ->orderByDesc('activity_log.created_at')
                ->limit(10)
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'message' => $event->description,
                        'user' => $event->username,
                        'time' => Carbon::parse($event->created_at)->diffForHumans(),
                    ];
                });
        }

        return response()->json([
            'active_users' => $activeUsers,
            'recent_events' => $recentEvents,
        ]);
    }
}
