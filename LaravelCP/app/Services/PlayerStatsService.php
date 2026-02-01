<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PlayerStatsService
{
    /**
     * Get comprehensive player statistics
     */
    public function getPlayerStats(User $user): array
    {
        return Cache::remember("player_stats:{$user->id}", 300, function() use ($user) {
            return [
                'overview' => $this->getOverview($user),
                'combat' => $this->getCombatStats($user),
                'crimes' => $this->getCrimeStats($user),
                'economy' => $this->getEconomyStats($user),
                'social' => $this->getSocialStats($user),
                'progression' => $this->getProgressionStats($user),
            ];
        });
    }

    /**
     * Get player overview stats
     */
    protected function getOverview(User $user): array
    {
        return [
            'level' => $user->level,
            'respect' => $user->respect,
            'cash' => $user->cash,
            'bank' => $user->bank,
            'total_worth' => $user->cash + $user->bank,
            'health' => $user->health,
            'max_health' => $user->max_health,
            'energy' => $user->energy,
            'max_energy' => $user->max_energy,
            'strength' => $user->strength,
            'defense' => $user->defense,
            'speed' => $user->speed,
            'rank' => $user->rank ?? 'Street Rat',
            'location' => $user->location->name ?? 'Unknown',
            'gang' => $user->gang ? [
                'name' => $user->gang->name,
                'role' => $user->gang_role,
            ] : null,
            'registration_date' => $user->created_at->toDateString(),
            'days_played' => $user->created_at->diffInDays(now()),
            'last_online' => $user->last_online?->diffForHumans(),
        ];
    }

    /**
     * Get combat statistics
     */
    protected function getCombatStats(User $user): array
    {
        $totalCombats = DB::table('combat_logs')
            ->where(function($q) use ($user) {
                $q->where('attacker_id', $user->id)
                  ->orWhere('defender_id', $user->id);
            })
            ->count();

        $wins = DB::table('combat_logs')
            ->where('winner_id', $user->id)
            ->count();

        $losses = $totalCombats - $wins;

        $asAttacker = DB::table('combat_logs')
            ->where('attacker_id', $user->id)
            ->count();

        $attackWins = DB::table('combat_logs')
            ->where('attacker_id', $user->id)
            ->where('winner_id', $user->id)
            ->count();

        $asDefender = DB::table('combat_logs')
            ->where('defender_id', $user->id)
            ->count();

        $defenseWins = DB::table('combat_logs')
            ->where('defender_id', $user->id)
            ->where('winner_id', $user->id)
            ->count();

        $cashWon = DB::table('combat_logs')
            ->where('winner_id', $user->id)
            ->sum('cash_stolen') ?? 0;

        $cashLost = DB::table('combat_logs')
            ->where(function($q) use ($user) {
                $q->where('attacker_id', $user->id)
                  ->orWhere('defender_id', $user->id);
            })
            ->where('winner_id', '!=', $user->id)
            ->sum('cash_stolen') ?? 0;

        return [
            'total_fights' => $totalCombats,
            'wins' => $wins,
            'losses' => $losses,
            'win_rate' => $totalCombats > 0 ? round(($wins / $totalCombats) * 100, 2) : 0,
            'attacks' => [
                'total' => $asAttacker,
                'successful' => $attackWins,
                'success_rate' => $asAttacker > 0 ? round(($attackWins / $asAttacker) * 100, 2) : 0,
            ],
            'defenses' => [
                'total' => $asDefender,
                'successful' => $defenseWins,
                'success_rate' => $asDefender > 0 ? round(($defenseWins / $asDefender) * 100, 2) : 0,
            ],
            'cash_won' => (int) $cashWon,
            'cash_lost' => (int) $cashLost,
            'net_cash' => (int) ($cashWon - $cashLost),
        ];
    }

    /**
     * Get crime statistics
     */
    protected function getCrimeStats(User $user): array
    {
        $totalCrimes = DB::table('crime_attempts')
            ->where('user_id', $user->id)
            ->count();

        $successfulCrimes = DB::table('crime_attempts')
            ->where('user_id', $user->id)
            ->where('success', true)
            ->count();

        $totalCashEarned = DB::table('crime_attempts')
            ->where('user_id', $user->id)
            ->where('success', true)
            ->sum('cash_earned') ?? 0;

        $totalRespectEarned = DB::table('crime_attempts')
            ->where('user_id', $user->id)
            ->where('success', true)
            ->sum('respect_earned') ?? 0;

        $jailCount = DB::table('crime_attempts')
            ->where('user_id', $user->id)
            ->where('jailed', true)
            ->count();

        $mostSuccessfulCrime = DB::table('crime_attempts')
            ->join('crimes', 'crime_attempts.crime_id', '=', 'crimes.id')
            ->select('crimes.name', DB::raw('COUNT(*) as count'))
            ->where('crime_attempts.user_id', $user->id)
            ->where('crime_attempts.success', true)
            ->groupBy('crimes.id', 'crimes.name')
            ->orderBy('count', 'desc')
            ->first();

        return [
            'total_attempts' => $totalCrimes,
            'successful' => $successfulCrimes,
            'failed' => $totalCrimes - $successfulCrimes,
            'success_rate' => $totalCrimes > 0 ? round(($successfulCrimes / $totalCrimes) * 100, 2) : 0,
            'total_cash_earned' => (int) $totalCashEarned,
            'total_respect_earned' => (int) $totalRespectEarned,
            'times_jailed' => $jailCount,
            'most_successful_crime' => $mostSuccessfulCrime?->name ?? 'None',
        ];
    }

    /**
     * Get economy statistics
     */
    protected function getEconomyStats(User $user): array
    {
        $bankDeposits = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'bank_deposit')
            ->count();

        $bankWithdrawals = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'bank_withdrawal')
            ->count();

        $moneyTransferred = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'bank_transfer')
            ->count();

        $itemsPurchased = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'item_purchase')
            ->count();

        $itemsSold = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'item_sold')
            ->count();

        $inventoryValue = DB::table('user_equipment')
            ->join('items', 'user_equipment.item_id', '=', 'items.id')
            ->where('user_equipment.user_id', $user->id)
            ->sum(DB::raw('items.price * user_equipment.quantity')) ?? 0;

        return [
            'current_cash' => $user->cash,
            'bank_balance' => $user->bank,
            'inventory_value' => (int) $inventoryValue,
            'total_net_worth' => $user->cash + $user->bank + (int) $inventoryValue,
            'bank_deposits' => $bankDeposits,
            'bank_withdrawals' => $bankWithdrawals,
            'money_transfers' => $moneyTransferred,
            'items_purchased' => $itemsPurchased,
            'items_sold' => $itemsSold,
        ];
    }

    /**
     * Get social statistics
     */
    protected function getSocialStats(User $user): array
    {
        $gangMembership = $user->gang;
        
        $forumPosts = DB::table('forum_posts')
            ->where('user_id', $user->id)
            ->count();

        $forumTopics = DB::table('forum_topics')
            ->where('user_id', $user->id)
            ->count();

        $bountyPlaced = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'bounty_placed')
            ->count();

        $bountyClaimed = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'bounty_claimed')
            ->count();

        return [
            'gang_membership' => $gangMembership ? [
                'name' => $gangMembership->name,
                'role' => $user->gang_role,
                'joined_at' => $user->gang_joined_at?->toDateString(),
            ] : null,
            'forum_posts' => $forumPosts,
            'forum_topics' => $forumTopics,
            'bounties_placed' => $bountyPlaced,
            'bounties_claimed' => $bountyClaimed,
        ];
    }

    /**
     * Get progression statistics
     */
    protected function getProgressionStats(User $user): array
    {
        $achievements = DB::table('player_achievements')
            ->where('user_id', $user->id)
            ->count();

        $missionsCompleted = DB::table('player_missions')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $gymTraining = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'gym_train')
            ->count();

        $locationsVisited = DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->where('type', 'travel')
            ->distinct('metadata->to_location_id')
            ->count();

        return [
            'current_level' => $user->level,
            'current_respect' => $user->respect,
            'achievements_unlocked' => $achievements,
            'missions_completed' => $missionsCompleted,
            'gym_training_sessions' => $gymTraining,
            'locations_visited' => $locationsVisited,
            'rank' => $user->rank ?? 'Street Rat',
        ];
    }

    /**
     * Get leaderboard position for player
     */
    public function getLeaderboardPosition(User $user): array
    {
        $respectRank = DB::table('users')
            ->where('respect', '>', $user->respect)
            ->count() + 1;

        $levelRank = DB::table('users')
            ->where('level', '>', $user->level)
            ->count() + 1;

        $wealthRank = DB::table('users')
            ->where(DB::raw('cash + bank'), '>', ($user->cash + $user->bank))
            ->count() + 1;

        return [
            'respect_rank' => $respectRank,
            'level_rank' => $levelRank,
            'wealth_rank' => $wealthRank,
        ];
    }

    /**
     * Clear stats cache for user
     */
    public function clearCache(User $user): void
    {
        Cache::forget("player_stats:{$user->id}");
    }
}
