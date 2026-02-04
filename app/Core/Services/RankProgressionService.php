<?php

namespace App\Core\Services;

use App\Core\Models\User;
use App\Core\Services\NotificationService;

class RankProgressionService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Rank titles with corresponding level requirements.
     */
    protected array $ranks = [
        1 => 'Street Rat',
        5 => 'Thug',
        10 => 'Hustler',
        15 => 'Enforcer',
        20 => 'Soldier',
        25 => 'Capo',
        30 => 'Made Man',
        35 => 'Underboss',
        40 => 'Crime Boss',
        50 => 'Don',
        60 => 'Godfather',
        75 => 'Criminal Mastermind',
        100 => 'Legend',
    ];

    /**
     * Calculate XP required for next level.
     */
    public function xpForLevel(int $level): int
    {
        // Formula: 100 * level^1.5
        return (int) (100 * pow($level, 1.5));
    }

    /**
     * Get total XP required to reach a specific level.
     */
    public function totalXpForLevel(int $targetLevel): int
    {
        $totalXp = 0;
        for ($level = 1; $level < $targetLevel; $level++) {
            $totalXp += $this->xpForLevel($level);
        }
        return $totalXp;
    }

    /**
     * Get rank title for a level.
     */
    public function getRankTitle(int $level): string
    {
        $rank = 'Street Rat';
        
        foreach ($this->ranks as $requiredLevel => $title) {
            if ($level >= $requiredLevel) {
                $rank = $title;
            } else {
                break;
            }
        }
        
        return $rank;
    }

    /**
     * Add experience to user and handle level-ups.
     */
    public function addExperience(User $user, int $amount, string $source = 'activity'): array
    {
        $user->experience += $amount;
        $levelsGained = 0;
        $oldLevel = $user->level;

        // Check for level-up(s)
        while ($user->experience >= $this->xpForLevel($user->level)) {
            $user->experience -= $this->xpForLevel($user->level);
            $user->level++;
            $levelsGained++;
            
            // Apply stat increases
            $this->applyLevelUpBonuses($user);
        }

        $user->save();

        // Send notifications for level-ups
        if ($levelsGained > 0) {
            $newRank = $this->getRankTitle($user->level);
            $oldRank = $this->getRankTitle($oldLevel);
            
            $this->notificationService->create(
                $user,
                'achievement',
                'Level Up!',
                "Congratulations! You've reached level {$user->level}.",
                [
                    'old_level' => $oldLevel,
                    'new_level' => $user->level,
                    'levels_gained' => $levelsGained,
                ]
            );

            // Rank up notification if rank changed
            if ($newRank !== $oldRank) {
                $this->notificationService->create(
                    $user,
                    'achievement',
                    'Rank Promotion!',
                    "You've been promoted to {$newRank}!",
                    [
                        'old_rank' => $oldRank,
                        'new_rank' => $newRank,
                        'level' => $user->level,
                    ]
                );

                // Update rank in database
                $user->rank = $newRank;
                $user->save();
            }
        }

        return [
            'xp_gained' => $amount,
            'levels_gained' => $levelsGained,
            'current_level' => $user->level,
            'current_xp' => $user->experience,
            'xp_for_next_level' => $this->xpForLevel($user->level),
            'current_rank' => $user->rank,
        ];
    }

    /**
     * Apply stat bonuses when leveling up.
     */
    protected function applyLevelUpBonuses(User $user): void
    {
        // Each level grants stat increases
        $user->max_health += 10;
        $user->max_energy += 5;
        $user->strength += 2;
        $user->defense += 2;
        $user->speed += 1;

        // Restore health and energy on level up
        $user->health = $user->max_health;
        $user->energy = $user->max_energy;

        // Every 5 levels, grant additional bonuses
        if ($user->level % 5 === 0) {
            $user->cash += 1000 * ($user->level / 5);
            $user->bullets += 10;
        }

        // Every 10 levels, grant significant bonuses
        if ($user->level % 10 === 0) {
            $user->respect += 10;
        }
    }

    /**
     * Get user's progress to next level.
     */
    public function getLevelProgress(User $user): array
    {
        $xpForNext = $this->xpForLevel($user->level);
        $percentage = ($user->experience / $xpForNext) * 100;

        return [
            'current_level' => $user->level,
            'current_xp' => $user->experience,
            'xp_for_next_level' => $xpForNext,
            'percentage' => round($percentage, 2),
            'current_rank' => $user->rank,
            'next_rank' => $this->getNextRank($user->level),
            'levels_to_next_rank' => $this->getLevelsToNextRank($user->level),
        ];
    }

    /**
     * Get next rank title.
     */
    protected function getNextRank(int $currentLevel): ?string
    {
        foreach ($this->ranks as $requiredLevel => $title) {
            if ($currentLevel < $requiredLevel) {
                return $title;
            }
        }
        return null; // Max rank reached
    }

    /**
     * Get levels until next rank.
     */
    protected function getLevelsToNextRank(int $currentLevel): ?int
    {
        foreach ($this->ranks as $requiredLevel => $title) {
            if ($currentLevel < $requiredLevel) {
                return $requiredLevel - $currentLevel;
            }
        }
        return null; // Max rank reached
    }

    /**
     * Get all ranks.
     */
    public function getAllRanks(): array
    {
        return $this->ranks;
    }

    /**
     * Get leaderboard by level.
     */
    public function getLevelLeaderboard(int $limit = 50): array
    {
        return User::orderBy('level', 'desc')
            ->orderBy('experience', 'desc')
            ->limit($limit)
            ->get(['id', 'username', 'level', 'experience', 'rank'])
            ->toArray();
    }
}
