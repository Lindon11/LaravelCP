<?php

namespace App\Core\Services;

use App\Core\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WebSocketService
{
    /**
     * Channel types
     */
    public const CHANNEL_PUBLIC = 'public';
    public const CHANNEL_PRIVATE = 'private';
    public const CHANNEL_PRESENCE = 'presence';

    /**
     * Predefined channels
     */
    public const CHANNELS = [
        'global' => self::CHANNEL_PUBLIC,
        'announcements' => self::CHANNEL_PUBLIC,
        'chat' => self::CHANNEL_PUBLIC,
        'user.{id}' => self::CHANNEL_PRIVATE,
        'gang.{id}' => self::CHANNEL_PRESENCE,
        'combat.{id}' => self::CHANNEL_PRIVATE,
        'admin' => self::CHANNEL_PRIVATE,
    ];

    /**
     * Broadcast an event to a channel
     */
    public function broadcast(string $channel, string $event, array $data = []): void
    {
        $payload = [
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];

        // Store in cache for polling fallback
        $this->storeMessage($channel, $payload);

        // Broadcast via configured driver (Redis, Pusher, etc.)
        broadcast(new \App\Core\Events\WebSocketBroadcast($channel, $event, $data));
    }

    /**
     * Broadcast to a specific user
     */
    public function toUser(User $user, string $event, array $data = []): void
    {
        $this->broadcast("user.{$user->id}", $event, $data);
    }

    /**
     * Broadcast to all online users
     */
    public function toAll(string $event, array $data = []): void
    {
        $this->broadcast('global', $event, $data);
    }

    /**
     * Broadcast to admins
     */
    public function toAdmins(string $event, array $data = []): void
    {
        $this->broadcast('admin', $event, $data);
    }

    /**
     * Broadcast game notification
     */
    public function notification(User $user, string $type, string $title, string $message, array $extra = []): void
    {
        $this->toUser($user, 'notification', array_merge([
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ], $extra));
    }

    /**
     * Broadcast chat message
     */
    public function chatMessage(string $channel, array $message): void
    {
        $this->broadcast("chat.{$channel}", 'message', $message);
    }

    /**
     * Broadcast combat update
     */
    public function combatUpdate(int $fightId, string $event, array $data): void
    {
        $this->broadcast("combat.{$fightId}", $event, $data);
    }

    /**
     * Broadcast gang event
     */
    public function gangEvent(int $gangId, string $event, array $data): void
    {
        $this->broadcast("gang.{$gangId}", $event, $data);
    }

    /**
     * Broadcast announcement
     */
    public function announcement(string $title, string $message, string $type = 'info'): void
    {
        $this->toAll('announcement', [
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }

    /**
     * Track user online status
     */
    public function setOnline(User $user): void
    {
        Cache::put("user_online:{$user->id}", true, now()->addMinutes(5));
        $this->updateOnlineUsers();
    }

    /**
     * Track user offline status
     */
    public function setOffline(User $user): void
    {
        Cache::forget("user_online:{$user->id}");
        $this->updateOnlineUsers();
    }

    /**
     * Check if user is online
     */
    public function isOnline(User $user): bool
    {
        return Cache::has("user_online:{$user->id}");
    }

    /**
     * Get online users count
     */
    public function getOnlineCount(): int
    {
        return Cache::get('online_users_count', 0);
    }

    /**
     * Update online users count
     */
    protected function updateOnlineUsers(): void
    {
        // This would typically be handled by Redis or presence channels
        $count = Cache::get('online_users_count', 0);
        $this->toAll('online_count', ['count' => $count]);
    }

    /**
     * Store message for polling fallback
     */
    protected function storeMessage(string $channel, array $payload): void
    {
        $key = "ws_messages:{$channel}";
        $messages = Cache::get($key, []);
        
        // Keep last 100 messages per channel
        $messages[] = $payload;
        if (count($messages) > 100) {
            $messages = array_slice($messages, -100);
        }

        Cache::put($key, $messages, now()->addHours(1));
    }

    /**
     * Get messages for polling fallback
     */
    public function getMessages(string $channel, ?string $since = null): array
    {
        $messages = Cache::get("ws_messages:{$channel}", []);

        if ($since) {
            $messages = array_filter($messages, fn($m) => $m['timestamp'] > $since);
        }

        return array_values($messages);
    }

    /**
     * Authorize user for private channel
     */
    public function authorizeChannel(User $user, string $channel): bool
    {
        // Parse channel name
        if (str_starts_with($channel, 'user.')) {
            $userId = (int) str_replace('user.', '', $channel);
            return $user->id === $userId;
        }

        if (str_starts_with($channel, 'gang.')) {
            $gangId = (int) str_replace('gang.', '', $channel);
            return $user->gang_id === $gangId;
        }

        if ($channel === 'admin') {
            return $user->hasRole('admin');
        }

        // Public channels are always authorized
        return true;
    }

    /**
     * Get presence channel members
     */
    public function getPresenceMembers(string $channel): array
    {
        return Cache::get("presence:{$channel}", []);
    }

    /**
     * Join presence channel
     */
    public function joinPresence(string $channel, User $user): void
    {
        $members = $this->getPresenceMembers($channel);
        $members[$user->id] = [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar ?? null,
            'joined_at' => now()->toIso8601String(),
        ];

        Cache::put("presence:{$channel}", $members, now()->addHours(1));

        $this->broadcast($channel, 'member_joined', [
            'user' => $members[$user->id],
            'members_count' => count($members),
        ]);
    }

    /**
     * Leave presence channel
     */
    public function leavePresence(string $channel, User $user): void
    {
        $members = $this->getPresenceMembers($channel);
        unset($members[$user->id]);

        Cache::put("presence:{$channel}", $members, now()->addHours(1));

        $this->broadcast($channel, 'member_left', [
            'user_id' => $user->id,
            'members_count' => count($members),
        ]);
    }
}
