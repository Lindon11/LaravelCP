<?php

namespace App\Services;

use App\Models\User;
use App\Models\PrivateMessage;
use Illuminate\Support\Collection;

class MessageService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send a private message.
     */
    public function sendMessage(User $sender, int $recipientId, string $subject, string $body): PrivateMessage
    {
        $recipient = User::findOrFail($recipientId);

        $message = PrivateMessage::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
        ]);

        // Send notification to recipient
        $this->notificationService->create(
            $recipient,
            'message',
            'New Message',
            "You have a new message from {$sender->username}: {$subject}",
            [
                'message_id' => $message->id,
                'sender_id' => $sender->id,
                'sender_username' => $sender->username,
            ]
        );

        return $message;
    }

    /**
     * Get user's inbox.
     */
    public function getInbox(User $user, int $perPage = 20): Collection
    {
        return PrivateMessage::inbox($user->id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->get();
    }

    /**
     * Get user's sent messages.
     */
    public function getSentMessages(User $user, int $perPage = 20): Collection
    {
        return PrivateMessage::sent($user->id)
            ->with('recipient')
            ->orderBy('created_at', 'desc')
            ->limit($perPage)
            ->get();
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(User $user): int
    {
        return PrivateMessage::inbox($user->id)
            ->unread()
            ->count();
    }

    /**
     * Read a message.
     */
    public function readMessage(User $user, int $messageId): PrivateMessage
    {
        $message = PrivateMessage::findOrFail($messageId);

        // Verify user is the recipient
        if ($message->recipient_id !== $user->id) {
            throw new \Exception('You are not authorized to read this message.');
        }

        $message->markAsRead();

        return $message;
    }

    /**
     * Delete a message (soft delete for user).
     */
    public function deleteMessage(User $user, int $messageId): bool
    {
        $message = PrivateMessage::findOrFail($messageId);

        // Mark as deleted for the appropriate user
        if ($message->sender_id === $user->id) {
            $message->update(['sender_deleted' => true]);
        } elseif ($message->recipient_id === $user->id) {
            $message->update(['recipient_deleted' => true]);
        } else {
            throw new \Exception('You are not authorized to delete this message.');
        }

        // If both deleted, actually delete the message
        if ($message->sender_deleted && $message->recipient_deleted) {
            $message->delete();
        }

        return true;
    }

    /**
     * Reply to a message.
     */
    public function replyToMessage(User $sender, int $originalMessageId, string $body): PrivateMessage
    {
        $originalMessage = PrivateMessage::findOrFail($originalMessageId);

        // Determine recipient (if sender is replying to received message, reply to original sender)
        $recipientId = $originalMessage->sender_id === $sender->id 
            ? $originalMessage->recipient_id 
            : $originalMessage->sender_id;

        $subject = 'Re: ' . $originalMessage->subject;

        return $this->sendMessage($sender, $recipientId, $subject, $body);
    }

    /**
     * Mark all messages as read.
     */
    public function markAllAsRead(User $user): int
    {
        return PrivateMessage::inbox($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete all messages from inbox.
     */
    public function deleteAllInbox(User $user): int
    {
        return PrivateMessage::inbox($user->id)
            ->update(['recipient_deleted' => true]);
    }

    /**
     * Delete all sent messages.
     */
    public function deleteAllSent(User $user): int
    {
        return PrivateMessage::sent($user->id)
            ->update(['sender_deleted' => true]);
    }

    /**
     * Search messages.
     */
    public function searchMessages(User $user, string $query, string $folder = 'inbox'): Collection
    {
        $baseQuery = $folder === 'inbox' 
            ? PrivateMessage::inbox($user->id)
            : PrivateMessage::sent($user->id);

        return $baseQuery
            ->where(function($q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                  ->orWhere('body', 'like', "%{$query}%");
            })
            ->with($folder === 'inbox' ? 'sender' : 'recipient')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Get conversation with a user.
     */
    public function getConversation(User $user, int $otherUserId, int $limit = 50): Collection
    {
        return PrivateMessage::where(function($query) use ($user, $otherUserId) {
                $query->where('sender_id', $user->id)
                      ->where('recipient_id', $otherUserId)
                      ->where('sender_deleted', false);
            })
            ->orWhere(function($query) use ($user, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('recipient_id', $user->id)
                      ->where('recipient_deleted', false);
            })
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Block user (prevent messages from them).
     */
    public function canSendMessage(User $sender, int $recipientId): bool
    {
        // Add logic here to check if sender is blocked by recipient
        // For now, always allow
        return true;
    }
}
