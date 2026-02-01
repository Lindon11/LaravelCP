<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a new notification for a user.
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $icon = null,
        ?string $link = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => $icon,
            'link' => $link,
        ]);
    }

    /**
     * Create money received notification
     */
    public function moneyReceived(User $recipient, User $sender, int $amount): Notification
    {
        return $this->create(
            user: $recipient,
            type: 'money_received',
            title: 'Money Received',
            message: "{$sender->username} sent you \$" . number_format($amount),
            data: [
                'sender_id' => $sender->id,
                'sender_username' => $sender->username,
                'amount' => $amount,
            ],
            icon: '💰',
            link: '/bank'
        );
    }

    /**
     * Create combat notification
     */
    public function combatNotification(User $user, User $opponent, bool $won, int $cashAmount): Notification
    {
        $message = $won 
            ? "You defeated {$opponent->username} and won \$" . number_format($cashAmount)
            : "{$opponent->username} defeated you and took \$" . number_format($cashAmount);
            
        return $this->create(
            user: $user,
            type: $won ? 'combat_won' : 'combat_lost',
            title: $won ? 'Combat Victory!' : 'Combat Defeat',
            message: $message,
            data: [
                'opponent_id' => $opponent->id,
                'opponent_username' => $opponent->username,
                'cash_amount' => $cashAmount,
                'won' => $won,
            ],
            icon: $won ? '⚔️' : '🩹',
            link: '/combat'
        );
    }

    /**
     * Create bounty notification
     */
    public function bountyPlaced(User $target, User $placer, int $amount): Notification
    {
        return $this->create(
            user: $target,
            type: 'bounty_placed',
            title: 'Bounty Placed!',
            message: "{$placer->username} placed a \$" . number_format($amount) . " bounty on you!",
            data: [
                'placer_id' => $placer->id,
                'placer_username' => $placer->username,
                'amount' => $amount,
            ],
            icon: '🎯',
            link: '/bounties'
        );
    }

    /**
     * Create gang invite notification
     */
    public function gangInvite(User $invitee, $gang, User $inviter): Notification
    {
        return $this->create(
            user: $invitee,
            type: 'gang_invite',
            title: 'Gang Invitation',
            message: "{$inviter->username} invited you to join {$gang->name}",
            data: [
                'gang_id' => $gang->id,
                'gang_name' => $gang->name,
                'inviter_id' => $inviter->id,
                'inviter_username' => $inviter->username,
            ],
            icon: '👥',
            link: '/gangs'
        );
    }

    /**
     * Create level up notification
     */
    public function levelUp(User $user, int $newLevel): Notification
    {
        return $this->create(
            user: $user,
            type: 'level_up',
            title: 'Level Up!',
            message: "Congratulations! You reached level {$newLevel}!",
            data: ['level' => $newLevel],
            icon: '⬆️',
            link: '/dashboard'
        );
    }

    /**
     * Create admin message notification
     */
    public function adminMessage(User $user, string $message, ?string $title = null): Notification
    {
        return $this->create(
            user: $user,
            type: 'admin_message',
            title: $title ?? 'Admin Message',
            message: $message,
            data: ['from' => 'Admin'],
            icon: '👤',
            link: null
        );
    }

    /**
     * Create achievement notification.
     */
    public function achievement(User $user, string $achievementName, string $description, ?string $reward = null): Notification
    {
        return $this->create(
            user: $user,
            type: 'achievement',
            title: 'Achievement Unlocked!',
            message: $description,
            data: [
                'achievement' => $achievementName,
                'reward' => $reward,
            ],
            icon: '🏆',
            link: '/achievements'
        );
    }

    /**
     * Create combat result notification.
     */
    public function combat(User $user, string $result, string $opponent, array $data = []): Notification
    {
        $title = $result === 'won' ? 'Combat Victory!' : 'Combat Defeat!';
        $icon = $result === 'won' ? '⚔️' : '💀';
        
        return $this->create(
            user: $user,
            type: 'combat',
            title: $title,
            message: "Combat against {$opponent} has ended.",
            data: array_merge(['result' => $result, 'opponent' => $opponent], $data),
            icon: $icon,
            link: '/combat/history'
        );
    }

    /**
     * Create private message notification.
     */
    public function message(User $user, string $from, int $messageId): Notification
    {
        return $this->create(
            user: $user,
            type: 'message',
            title: 'New Message',
            message: "You have a new message from {$from}",
            data: ['message_id' => $messageId, 'from' => $from],
            icon: '✉️',
            link: "/messages/{$messageId}"
        );
    }

    /**
     * Create admin announcement notification.
     */
    public function announcement(User $user, string $title, string $message, ?string $link = null): Notification
    {
        return $this->create(
            user: $user,
            type: 'announcement',
            title: $title,
            message: $message,
            icon: '📢',
            link: $link
        );
    }

    /**
     * Create system notification.
     */
    public function system(User $user, string $title, string $message, ?array $data = null): Notification
    {
        return $this->create(
            user: $user,
            type: 'system',
            title: $title,
            message: $message,
            data: $data,
            icon: 'ℹ️'
        );
    }

    /**
     * Get all notifications for a user.
     */
    public function getAll(User $user, int $limit = 50): Collection
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(User $user, int $limit = 50): Collection
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notification count.
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     */
    public function delete(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->delete();
            return true;
        }
        
        return false;
    }

    /**
     * Delete all notifications for a user.
     */
    public function deleteAll(User $user): int
    {
        return Notification::where('user_id', $user->id)->delete();
    }

    /**
     * Delete all read notifications for a user.
     */
    public function deleteRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->read()
            ->delete();
    }

    /**
     * Delete old notifications (older than X days).
     */
    public function deleteOld(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Broadcast notification to all users.
     */
    public function broadcastToAll(string $title, string $message, ?string $link = null): int
    {
        $count = 0;
        
        User::chunk(100, function ($users) use ($title, $message, $link, &$count) {
            foreach ($users as $user) {
                $this->announcement($user, $title, $message, $link);
                $count++;
            }
        });
        
        return $count;
    }
}
