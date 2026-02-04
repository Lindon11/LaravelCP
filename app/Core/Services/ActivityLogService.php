<?php

namespace App\Core\Services;

use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class ActivityLogService
{
    /**
     * Activity types
     */
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_REGISTER = 'register';
    const TYPE_CRIME_ATTEMPT = 'crime_attempt';
    const TYPE_COMBAT = 'combat';
    const TYPE_BANK_DEPOSIT = 'bank_deposit';
    const TYPE_BANK_WITHDRAWAL = 'bank_withdrawal';
    const TYPE_BANK_TRANSFER = 'bank_transfer';
    const TYPE_GYM_TRAIN = 'gym_train';
    const TYPE_TRAVEL = 'travel';
    const TYPE_ITEM_PURCHASE = 'item_purchase';
    const TYPE_ITEM_SOLD = 'item_sold';
    const TYPE_BOUNTY_PLACED = 'bounty_placed';
    const TYPE_BOUNTY_CLAIMED = 'bounty_claimed';
    const TYPE_GANG_JOIN = 'gang_join';
    const TYPE_GANG_LEAVE = 'gang_leave';
    const TYPE_DRUG_BUY = 'drug_buy';
    const TYPE_DRUG_SELL = 'drug_sell';
    const TYPE_THEFT_ATTEMPT = 'theft_attempt';
    const TYPE_RACE_JOINED = 'race_joined';
    const TYPE_ORGANIZED_CRIME = 'organized_crime';
    const TYPE_ADMIN_ACTION = 'admin_action';
    const TYPE_BANNED = 'banned';
    const TYPE_UNBANNED = 'unbanned';

    /**
     * Log an activity
     */
    public function log(User $user, string $type, string $description, array $metadata = []): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => $user->id,
            'type' => $type,
            'description' => $description,
            'metadata' => json_encode($metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log login
     */
    public function logLogin(User $user): void
    {
        $this->log($user, self::TYPE_LOGIN, "User logged in");
    }

    /**
     * Log logout
     */
    public function logLogout(User $user): void
    {
        $this->log($user, self::TYPE_LOGOUT, "User logged out");
    }

    /**
     * Log registration
     */
    public function logRegistration(User $user): void
    {
        $this->log($user, self::TYPE_REGISTER, "New user registered");
    }

    /**
     * Log crime attempt
     */
    public function logCrime(User $user, $crime, bool $success, int $cashGained, int $respectGained): void
    {
        $this->log(
            $user,
            self::TYPE_CRIME_ATTEMPT,
            "Attempted {$crime->name}: " . ($success ? 'Success' : 'Failed'),
            [
                'crime_id' => $crime->id,
                'crime_name' => $crime->name,
                'success' => $success,
                'cash_gained' => $cashGained,
                'respect_gained' => $respectGained,
            ]
        );
    }

    /**
     * Log combat
     */
    public function logCombat(User $attacker, User $defender, bool $attackerWon, int $cashStolen): void
    {
        $this->log(
            $attacker,
            self::TYPE_COMBAT,
            "Attacked {$defender->username}: " . ($attackerWon ? 'Won' : 'Lost'),
            [
                'defender_id' => $defender->id,
                'defender_username' => $defender->username,
                'won' => $attackerWon,
                'cash_stolen' => $cashStolen,
            ]
        );

        $this->log(
            $defender,
            self::TYPE_COMBAT,
            "Defended against {$attacker->username}: " . ($attackerWon ? 'Lost' : 'Won'),
            [
                'attacker_id' => $attacker->id,
                'attacker_username' => $attacker->username,
                'won' => !$attackerWon,
                'cash_lost' => $cashStolen,
            ]
        );
    }

    /**
     * Log bank transaction
     */
    public function logBankTransaction(User $user, string $type, int $amount, ?int $recipientId = null): void
    {
        $description = match($type) {
            'deposit' => "Deposited \$" . number_format($amount),
            'withdraw' => "Withdrew \$" . number_format($amount),
            'transfer' => "Transferred \$" . number_format($amount),
            default => "Bank transaction"
        };

        $metadata = ['amount' => $amount];
        if ($recipientId) {
            $metadata['recipient_id'] = $recipientId;
        }

        $this->log($user, 'bank_' . $type, $description, $metadata);
    }

    /**
     * Log gym training
     */
    public function logGymTraining(User $user, string $stat, int $cost): void
    {
        $this->log(
            $user,
            self::TYPE_GYM_TRAIN,
            "Trained {$stat} for \$" . number_format($cost),
            [
                'stat' => $stat,
                'cost' => $cost,
            ]
        );
    }

    /**
     * Log travel
     */
    public function logTravel(User $user, $fromLocation, $toLocation): void
    {
        $this->log(
            $user,
            self::TYPE_TRAVEL,
            "Traveled from {$fromLocation->name} to {$toLocation->name}",
            [
                'from_location_id' => $fromLocation->id,
                'from_location' => $fromLocation->name,
                'to_location_id' => $toLocation->id,
                'to_location' => $toLocation->name,
            ]
        );
    }

    /**
     * Log item purchase/sale
     */
    public function logItemTransaction(User $user, $item, string $action, int $quantity, int $totalCost): void
    {
        $description = $action === 'purchase' 
            ? "Purchased {$quantity}x {$item->name} for \$" . number_format($totalCost)
            : "Sold {$quantity}x {$item->name} for \$" . number_format($totalCost);

        $this->log(
            $user,
            $action === 'purchase' ? self::TYPE_ITEM_PURCHASE : self::TYPE_ITEM_SOLD,
            $description,
            [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'quantity' => $quantity,
                'total_cost' => $totalCost,
            ]
        );
    }

    /**
     * Log bounty action
     */
    public function logBounty(User $user, User $target, string $action, int $amount): void
    {
        $description = $action === 'placed'
            ? "Placed \$" . number_format($amount) . " bounty on {$target->username}"
            : "Claimed \$" . number_format($amount) . " bounty on {$target->username}";

        $this->log(
            $user,
            $action === 'placed' ? self::TYPE_BOUNTY_PLACED : self::TYPE_BOUNTY_CLAIMED,
            $description,
            [
                'target_id' => $target->id,
                'target_username' => $target->username,
                'amount' => $amount,
            ]
        );
    }

    /**
     * Log admin action
     */
    public function logAdminAction(User $admin, string $action, ?User $targetUser = null, array $details = []): void
    {
        $description = $targetUser
            ? "Admin action on {$targetUser->username}: {$action}"
            : "Admin action: {$action}";

        $metadata = array_merge(['action' => $action], $details);
        if ($targetUser) {
            $metadata['target_user_id'] = $targetUser->id;
            $metadata['target_username'] = $targetUser->username;
        }

        $this->log($admin, self::TYPE_ADMIN_ACTION, $description, $metadata);
    }

    /**
     * Get user activity history
     */
    public function getUserActivity(User $user, int $limit = 50)
    {
        return DB::table('activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activity (for admin)
     */
    public function getRecentActivity(int $limit = 100, ?string $type = null)
    {
        $query = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.username')
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit($limit);

        if ($type) {
            $query->where('activity_logs.type', $type);
        }

        return $query->get();
    }

    /**
     * Get suspicious activity
     */
    public function getSuspiciousActivity(): array
    {
        // Multiple logins from different IPs in short time
        $multipleIps = DB::table('activity_logs')
            ->select('user_id', DB::raw('COUNT(DISTINCT ip_address) as ip_count'))
            ->where('type', self::TYPE_LOGIN)
            ->where('created_at', '>=', now()->subHours(1))
            ->groupBy('user_id')
            ->having('ip_count', '>', 3)
            ->get();

        // Rapid transactions (possible bot)
        $rapidActions = DB::table('activity_logs')
            ->select('user_id', DB::raw('COUNT(*) as action_count'))
            ->whereIn('type', [
                self::TYPE_CRIME_ATTEMPT,
                self::TYPE_COMBAT,
                self::TYPE_GYM_TRAIN
            ])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->groupBy('user_id')
            ->having('action_count', '>', 50)
            ->get();

        return [
            'multiple_ips' => $multipleIps,
            'rapid_actions' => $rapidActions,
        ];
    }

    /**
     * Clean old activity logs (older than 90 days)
     */
    public function cleanOldLogs(): int
    {
        return DB::table('activity_logs')
            ->where('created_at', '<', now()->subDays(90))
            ->delete();
    }
}
