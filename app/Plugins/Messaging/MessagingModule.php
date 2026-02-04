<?php

namespace App\Plugins\Messaging;

use App\Plugins\Plugin;
use App\Plugins\Messaging\Models\Message;
use App\Plugins\Messaging\Models\Conversation;

/**
 * Messaging Module
 *
 * Private messaging system between players
 */
class MessagingModule extends Plugin
{
    protected string $name = 'Messaging';

    public function construct(): void
    {
        $this->config = [
            'max_message_length' => 5000,
            'max_subject_length' => 100,
            'messages_per_page' => 20,
            'allow_attachments' => false,
            'cooldown_seconds' => 5,
        ];
    }

    /**
     * Get user's inbox
     */
    public function getInbox(int $userId, int $page = 1): array
    {
        return Message::where('recipient_id', $userId)
            ->where('recipient_deleted', false)
            ->with('sender:id,username,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate($this->config['messages_per_page'], ['*'], 'page', $page)
            ->toArray();
    }

    /**
     * Get user's sent messages
     */
    public function getSent(int $userId, int $page = 1): array
    {
        return Message::where('sender_id', $userId)
            ->where('sender_deleted', false)
            ->with('recipient:id,username,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate($this->config['messages_per_page'], ['*'], 'page', $page)
            ->toArray();
    }

    /**
     * Get conversation with a user
     */
    public function getConversation(int $userId, int $otherUserId, int $page = 1): array
    {
        return Message::where(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $userId)->where('recipient_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('sender_id', $otherUserId)->where('recipient_id', $userId);
        })
            ->orderBy('created_at', 'desc')
            ->paginate($this->config['messages_per_page'], ['*'], 'page', $page)
            ->toArray();
    }

    /**
     * Send a message
     */
    public function sendMessage(int $senderId, int $recipientId, string $subject, string $body): array
    {
        if (strlen($body) > $this->config['max_message_length']) {
            return ['success' => false, 'message' => 'Message too long'];
        }

        if (strlen($subject) > $this->config['max_subject_length']) {
            return ['success' => false, 'message' => 'Subject too long'];
        }

        $message = Message::create([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'read_at' => null,
        ]);

        return ['success' => true, 'message' => 'Message sent', 'data' => $message];
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        return Message::where('recipient_id', $userId)
            ->whereNull('read_at')
            ->where('recipient_deleted', false)
            ->count();
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        if (!$user) {
            return ['unread' => 0, 'total' => 0];
        }

        return [
            'unread' => $this->getUnreadCount($user->id),
            'total' => Message::where('recipient_id', $user->id)
                ->where('recipient_deleted', false)
                ->count(),
        ];
    }
}
