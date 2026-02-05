<?php

namespace App\Plugins\Leaderboards\Services;

use App\Core\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    protected int $cacheTtl = 300; // 5 minutes

    /**
     * Get a leaderboard by type
     */
    public function getLeaderboard(string $type, int $perPage = 50)
    {
        $cacheKey = "leaderboard:{$type}:page:" . request('page', 1);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($type, $perPage) {
            return match ($type) {
                'level' => $this->getLevelLeaderboard($perPage),
                'wealth' => $this->getWealthLeaderboard($perPage),
                'combat' => $this->getCombatLeaderboard($perPage),
                'crimes' => $this->getCrimesLeaderboard($perPage),
                'respect' => $this->getRespectLeaderboard($perPage),
                'gang' => $this->getGangLeaderboard($perPage),
                default => throw new \Exception("Unknown leaderboard type: {$type}"),
            };
        });
    }

    /**
     * Get a player's rank on a specific leaderboard
     */
    public function getPlayerRank(User $user, string $type): ?int
    {
        $column = match ($type) {
            'level' => 'level',
            'wealth' => DB::raw('(cash + bank)'),
            'combat' => 'combat_wins',
            'crimes' => 'crimes_completed',
            'respect' => 'respect',
            default => null,
        };

        if (!$column) {
            return null;
        }

        if ($type === 'wealth') {
            return User::where(DB::raw('(cash + bank)'), '>', $user->cash + ($user->bank ?? 0))->count() + 1;
        }

        $value = $user->{$column} ?? 0;
        return User::where($column, '>', $value)->count() + 1;
    }

    /**
     * Get all available leaderboard types
     */
    public function getTypes(): array
    {
        return [
            ['id' => 'level', 'name' => 'Level', 'description' => 'Ranked by character level'],
            ['id' => 'wealth', 'name' => 'Wealth', 'description' => 'Ranked by total net worth'],
            ['id' => 'combat', 'name' => 'Combat', 'description' => 'Ranked by combat wins'],
            ['id' => 'crimes', 'name' => 'Crimes', 'description' => 'Ranked by crimes completed'],
            ['id' => 'respect', 'name' => 'Respect', 'description' => 'Ranked by respect points'],
            ['id' => 'gang', 'name' => 'Gangs', 'description' => 'Top gangs by total respect'],
        ];
    }

    protected function getLevelLeaderboard(int $perPage)
    {
        return User::select('id', 'username', 'level', 'exp', 'avatar')
            ->orderByDesc('level')
            ->orderByDesc('exp')
            ->paginate($perPage);
    }

    protected function getWealthLeaderboard(int $perPage)
    {
        return User::select('id', 'username', 'avatar')
            ->selectRaw('(cash + COALESCE(bank, 0)) as total_wealth')
            ->orderByDesc('total_wealth')
            ->paginate($perPage);
    }

    protected function getCombatLeaderboard(int $perPage)
    {
        return User::select('id', 'username', 'combat_wins', 'combat_losses', 'avatar')
            ->selectRaw('CASE WHEN combat_losses > 0 THEN ROUND(combat_wins / combat_losses, 2) ELSE combat_wins END as win_ratio')
            ->orderByDesc('combat_wins')
            ->paginate($perPage);
    }

    protected function getCrimesLeaderboard(int $perPage)
    {
        return User::select('id', 'username', 'crimes_completed', 'crimes_failed', 'avatar')
            ->selectRaw('CASE WHEN (crimes_completed + crimes_failed) > 0 THEN ROUND(crimes_completed * 100.0 / (crimes_completed + crimes_failed), 1) ELSE 0 END as success_rate')
            ->orderByDesc('crimes_completed')
            ->paginate($perPage);
    }

    protected function getRespectLeaderboard(int $perPage)
    {
        return User::select('id', 'username', 'respect', 'level', 'avatar')
            ->orderByDesc('respect')
            ->paginate($perPage);
    }

    protected function getGangLeaderboard(int $perPage)
    {
        return DB::table('gangs')
            ->select('gangs.id', 'gangs.name', 'gangs.tag', 'gangs.level')
            ->selectRaw('SUM(users.respect) as total_respect')
            ->selectRaw('COUNT(gang_members.user_id) as member_count')
            ->leftJoin('gang_members', 'gangs.id', '=', 'gang_members.gang_id')
            ->leftJoin('users', 'gang_members.user_id', '=', 'users.id')
            ->groupBy('gangs.id', 'gangs.name', 'gangs.tag', 'gangs.level')
            ->orderByDesc('total_respect')
            ->paginate($perPage);
    }

    /**
     * Clear leaderboard cache
     */
    public function clearCache(?string $type = null): void
    {
        if ($type) {
            Cache::forget("leaderboard:{$type}:page:1");
        } else {
            foreach ($this->getTypes() as $t) {
                Cache::forget("leaderboard:{$t['id']}:page:1");
            }
        }
    }
}
