<?php

namespace App\Modules\Leaderboards;

use App\Modules\Module;

/**
 * Leaderboards Module
 * 
 * Handles competitive leaderboards for player rankings
 * Provides various leaderboard types (level, cash, respect, etc.)
 */
class LeaderboardsModule extends Module
{
    protected string $name = 'Leaderboards';
    
    public function construct(): void
    {
        $this->config = [
            'top_players_count' => 50,
            'cache_duration' => 300, // 5 minutes
            'leaderboard_types' => [
                'level' => 'Top Players by Level',
                'respect' => 'Most Respected',
                'cash' => 'Richest Players',
                'networth' => 'Net Worth',
            ],
        ];
    }
    
    /**
     * Get leaderboard by type
     */
    public function getLeaderboard(string $type, int $limit = 50): array
    {
        $data = match($type) {
            'level' => $this->getLevelLeaderboard($limit),
            'respect' => $this->getRespectLeaderboard($limit),
            'cash' => $this->getCashLeaderboard($limit),
            'networth' => $this->getNetworthLeaderboard($limit),
            default => [],
        };
        
        return $this->applyModuleHook('alterLeaderboard', [
            'type' => $type,
            'data' => $data,
        ]);
    }
    
    /**
     * Get all leaderboards
     */
    public function getAllLeaderboards(): array
    {
        return [
            'level' => $this->getLevelLeaderboard(50),
            'respect' => $this->getRespectLeaderboard(50),
            'cash' => $this->getCashLeaderboard(50),
            'networth' => $this->getNetworthLeaderboard(50),
        ];
    }
    
    private function getLevelLeaderboard(int $limit)
    {
        return \App\Models\User::select('id', 'username', 'level', 'experience', 'rank')
            ->orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->limit($limit)
            ->get();
    }
    
    private function getRespectLeaderboard(int $limit)
    {
        return \App\Models\User::select('id', 'username', 'respect', 'level', 'rank')
            ->orderBy('respect', 'desc')
            ->limit($limit)
            ->get();
    }
    
    private function getCashLeaderboard(int $limit)
    {
        return \App\Models\User::select('id', 'username', 'cash', 'level', 'rank')
            ->orderBy('cash', 'desc')
            ->limit($limit)
            ->get();
    }
    
    private function getNetworthLeaderboard(int $limit)
    {
        return \App\Models\User::selectRaw('id, username, (cash + bank) as networth, level, rank')
            ->orderByRaw('(cash + bank) DESC')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'total_players' => \App\Models\User::count(),
            ];
        }
        
        return [
            'total_players' => \App\Models\User::count(),
            'your_rank' => $this->getUserRank($user),
        ];
    }
    
    private function getUserRank($user): int
    {
        return \App\Models\User::where('level', '>', $user->level)
            ->orWhere(function($q) use ($user) {
                $q->where('level', $user->level)
                  ->where('experience', '>', $user->experience);
            })
            ->count() + 1;
    }
}
