<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class CacheService
{
    /**
     * Cache durations in seconds
     */
    const DURATION_SHORT = 300; // 5 minutes
    const DURATION_MEDIUM = 1800; // 30 minutes
    const DURATION_LONG = 3600; // 1 hour
    const DURATION_DAY = 86400; // 24 hours

    /**
     * Cache leaderboard data
     */
    public function cacheLeaderboard(string $type, $data, int $duration = self::DURATION_MEDIUM): void
    {
        Cache::put("leaderboard:{$type}", $data, $duration);
    }

    /**
     * Get cached leaderboard
     */
    public function getLeaderboard(string $type)
    {
        return Cache::get("leaderboard:{$type}");
    }

    /**
     * Cache user data
     */
    public function cacheUser(User $user, int $duration = self::DURATION_SHORT): void
    {
        Cache::put("user:{$user->id}", $user->toArray(), $duration);
    }

    /**
     * Get cached user
     */
    public function getUser(int $userId)
    {
        return Cache::get("user:{$userId}");
    }

    /**
     * Cache online users count
     */
    public function cacheOnlineUsers(int $count, int $duration = self::DURATION_SHORT): void
    {
        Cache::put('stats:online_users', $count, $duration);
    }

    /**
     * Get cached online users count
     */
    public function getOnlineUsers(): ?int
    {
        return Cache::get('stats:online_users');
    }

    /**
     * Cache game statistics
     */
    public function cacheGameStats(array $stats, int $duration = self::DURATION_MEDIUM): void
    {
        Cache::put('stats:game', $stats, $duration);
    }

    /**
     * Get cached game statistics
     */
    public function getGameStats(): ?array
    {
        return Cache::get('stats:game');
    }

    /**
     * Cache module data
     */
    public function cacheModules(array $modules, int $duration = self::DURATION_LONG): void
    {
        Cache::put('modules:all', $modules, $duration);
    }

    /**
     * Get cached modules
     */
    public function getModules(): ?array
    {
        return Cache::get('modules:all');
    }

    /**
     * Cache settings
     */
    public function cacheSettings(array $settings, int $duration = self::DURATION_LONG): void
    {
        Cache::put('settings:game', $settings, $duration);
    }

    /**
     * Get cached settings
     */
    public function getSettings(): ?array
    {
        return Cache::get('settings:game');
    }

    /**
     * Clear user-specific caches
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget("user:{$userId}");
        Cache::forget("player_stats:{$userId}");
        Cache::forget("user_equipment:{$userId}");
    }

    /**
     * Clear all leaderboard caches
     */
    public function clearLeaderboards(): void
    {
        $types = ['respect', 'level', 'wealth', 'crimes', 'combat'];
        foreach ($types as $type) {
            Cache::forget("leaderboard:{$type}");
        }
    }

    /**
     * Clear all game caches
     */
    public function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Warm up critical caches
     */
    public function warmUp(): array
    {
        $warmed = [];

        // Cache online users
        $onlineCount = User::where('last_online', '>=', now()->subMinutes(15))->count();
        $this->cacheOnlineUsers($onlineCount);
        $warmed[] = 'online_users';

        // Cache game stats
        $gameStats = [
            'total_users' => User::count(),
            'active_gangs' => \App\Models\Gang::count(),
            'total_crimes' => \App\Models\CrimeAttempt::count(),
            'total_combats' => \App\Models\CombatLog::count(),
        ];
        $this->cacheGameStats($gameStats);
        $warmed[] = 'game_stats';

        // Cache modules
        $modules = \App\Models\Module::where('enabled', true)->get()->toArray();
        $this->cacheModules($modules);
        $warmed[] = 'modules';

        return [
            'success' => true,
            'warmed' => $warmed,
            'message' => 'Cache warmed up successfully',
        ];
    }
}
