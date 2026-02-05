<?php

namespace App\Plugins\Messaging\Services;

use App\Plugins\Messaging\Models\Conversation;
use App\Plugins\Messaging\Models\Message;
use App\Plugins\Messaging\Models\ConversationParticipant;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /**
     * Get all conversations for a user
     */
    public function getConversations(User $user, int $perPage = 20)
    {
        return Conversation::whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['participants.user:id,username,avatar', 'latestMessage'])
            ->withCount('unreadMessages')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    /**
     * Get a single conversation with messages
     */
    public function getConversation(Conversation $conversation, User $user, int $perPage = 50)
    {
        $this->ensureParticipant($conversation, $user);

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return [
            'conversation' => $conversation->load('participants.user:id,username,avatar'),
            'messages' => $conversation->messages()
                ->with('sender:id,username,avatar')
                ->orderByDesc('created_at')
                ->paginate($perPage),
        ];
    }

    /**
     * Create a new conversation
     */
    public function createConversation(User $creator, array $data): Conversation
    {
        return DB::transaction(function () use ($creator, $data) {
            $conversation = Conversation::create([
                'title' => $data['title'] ?? null,
                'type' => count($data['participants'] ?? []) > 1 ? 'group' : 'direct',
                'created_by' => $creator->id,
            ]);

            // Add creator as participant
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $creator->id,
                'role' => 'owner',
            ]);

            // Add other participants
            foreach ($data['participants'] as $userId) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'role' => 'member',
                ]);
            }

            // Send initial message if provided
            if (!empty($data['message'])) {
                $this->sendMessage($conversation, $creator, ['body' => $data['message']]);
            }

            return $conversation->load('participants.user:id,username,avatar');
        });
    }

    /**
     * Send a message in a conversation
     */
    public function sendMessage(Conversation $conversation, User $sender, array $data): Message
    {
        $this->ensureParticipant($conversation, $sender);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'body' => $data['body'],
            'type' => $data['type'] ?? 'text',
        ]);

        $conversation->touch();

        return $message->load('sender:id,username,avatar');
    }

    /**
     * Delete a message (soft delete, marks as deleted for the user)
     */
    public function deleteMessage(Message $message, User $user): void
    {
        if ($message->user_id !== $user->id) {
            throw new \Exception('You can only delete your own messages.');
        }

        $message->update(['deleted_at' => now()]);
    }

    /**
     * Leave a conversation
     */
    public function leaveConversation(Conversation $conversation, User $user): void
    {
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->delete();

        // If no participants left, delete the conversation
        if ($conversation->participants()->count() === 0) {
            $conversation->delete();
        }
    }

    /**
     * Block a user from messaging
     */
    public function blockUser(User $blocker, int $blockedId): void
    {
        DB::table('message_blocks')->updateOrInsert(
            ['user_id' => $blocker->id, 'blocked_user_id' => $blockedId],
            ['created_at' => now()]
        );
    }

    /**
     * Unblock a user
     */
    public function unblockUser(User $blocker, int $blockedId): void
    {
        DB::table('message_blocks')
            ->where('user_id', $blocker->id)
            ->where('blocked_user_id', $blockedId)
            ->delete();
    }

    /**
     * Check if user is a participant in the conversation
     */
    protected function ensureParticipant(Conversation $conversation, User $user): void
    {
        $isParticipant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isParticipant) {
            throw new \Exception('You are not a participant in this conversation.');
        }
    }
}
