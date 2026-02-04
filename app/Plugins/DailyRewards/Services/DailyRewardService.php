<?php

namespace App\Plugins\DailyRewards\Services;

use App\Core\Models\User;
use App\Plugins\DailyReward\Models\DailyReward;

class DailyRewardService
{
    public function getRewardInfo(User $user): array
    {
        $reward = $user->dailyReward ?? DailyReward::create(['user_id' => $user->id]);

        return [
            'can_claim' => $reward->canClaim(),
            'streak' => $reward->streak,
            'last_claimed' => $reward->last_claimed_at,
            'next_reward' => $this->calculateReward($reward->streak + 1),
        ];
    }

    public function claimReward(User $user): array
    {
        $reward = $user->dailyReward ?? DailyReward::create(['user_id' => $user->id]);

        if (!$reward->canClaim()) {
            throw new \Exception('You have already claimed your daily reward today!');
        }

        // Check if streak should be reset
        if ($reward->shouldResetStreak()) {
            $reward->streak = 1;
        } else {
            $reward->streak++;
        }

        // Calculate rewards based on streak
        $rewards = $this->calculateReward($reward->streak);

        // Give rewards
        $user->cash += $rewards['cash'];
        $user->experience += $rewards['xp'];
        
        if ($rewards['bullets'] > 0) {
            $user->bullets += $rewards['bullets'];
        }

        $user->save();

        $reward->last_claimed_at = now();
        $reward->save();

        return [
            'message' => "Day {$reward->streak} reward claimed!",
            'streak' => $reward->streak,
            'rewards' => $rewards,
        ];
    }

    private function calculateReward(int $streak): array
    {
        $baseCash = 1000;
        $baseXp = 50;

        $rewards = [
            'cash' => $baseCash * $streak,
            'xp' => $baseXp * $streak,
            'bullets' => 0,
        ];

        // Bonus rewards at milestones
        if ($streak == 3) {
            $rewards['bullets'] = 50;
        } elseif ($streak == 7) {
            $rewards['cash'] *= 2;
            $rewards['bullets'] = 100;
        } elseif ($streak >= 14) {
            $rewards['cash'] *= 3;
            $rewards['xp'] *= 2;
            $rewards['bullets'] = 200;
        }

        return $rewards;
    }
}
