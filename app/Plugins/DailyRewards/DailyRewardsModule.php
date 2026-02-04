<?php

namespace App\Plugins\DailyRewards;

use App\Plugins\Plugin;

/**
 * Daily Rewards Module
 *
 * Handles daily login rewards system
 * Encourages daily engagement with progressive rewards
 */
class DailyRewardsModule extends Plugin
{
    protected string $name = 'DailyRewards';

    public function construct(): void
    {
        $this->config = [
            'base_cash_reward' => 5000,
            'base_xp_reward' => 100,
            'streak_bonus_multiplier' => 1.1, // 10% increase per day
            'max_streak_bonus' => 3.0, // Max 3x multiplier
            'streak_milestones' => [
                7 => ['bullets' => 100, 'bonus_cash' => 10000],
                30 => ['bullets' => 500, 'bonus_cash' => 50000],
                90 => ['bullets' => 1000, 'bonus_cash' => 100000],
            ],
        ];
    }

    /**
     * Calculate daily reward for player
     */
    public function calculateReward($user): array
    {
        $streak = $user->daily_login_streak ?? 1;

        // Calculate streak multiplier
        $multiplier = min(
            $this->config['max_streak_bonus'],
            pow($this->config['streak_bonus_multiplier'], $streak - 1)
        );

        $cash = (int)($this->config['base_cash_reward'] * $multiplier);
        $xp = (int)($this->config['base_xp_reward'] * $multiplier);

        $rewards = [
            'cash' => $cash,
            'xp' => $xp,
            'bullets' => 0,
            'streak' => $streak,
        ];

        // Check for streak milestones
        if (isset($this->config['streak_milestones'][$streak])) {
            $milestone = $this->config['streak_milestones'][$streak];
            $rewards['bullets'] = $milestone['bullets'];
            $rewards['cash'] += $milestone['bonus_cash'];
            $rewards['milestone'] = true;
        }

        return $this->applyModuleHook('alterDailyReward', [
            'rewards' => $rewards,
            'user' => $user,
        ]);
    }

    /**
     * Check if player can claim reward
     */
    public function canClaim($user): bool
    {
        if (!$user->last_daily_reward) {
            return true;
        }

        $lastClaim = \Carbon\Carbon::parse($user->last_daily_reward);
        return $lastClaim->lt(now()->startOfDay());
    }

    /**
     * Get module stats for sidebar
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        if (!$user) {
            return [
                'can_claim' => false,
                'streak' => 0,
            ];
        }

        return [
            'can_claim' => $this->canClaim($user),
            'streak' => $user->daily_login_streak ?? 0,
            'next_milestone' => $this->getNextMilestone($user),
        ];
    }

    private function getNextMilestone($user): ?int
    {
        $streak = $user->daily_login_streak ?? 0;

        foreach (array_keys($this->config['streak_milestones']) as $milestone) {
            if ($milestone > $streak) {
                return $milestone;
            }
        }

        return null;
    }
}
