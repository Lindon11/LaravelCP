<?php

namespace App\Plugins\Chat;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Chat\Models\ChatChannel;
use App\Plugins\Chat\Models\ChatMessage;
use App\Plugins\Chat\Models\ChannelMember;
use App\Plugins\Chat\Models\MessageReaction;
use Illuminate\Support\Str;

/**
 * Chat Module
 * 
 * Handles real-time chat, channels, private messages, reactions
 */
class ChatModule extends Plugin
{
    protected string $name = 'Chat';
    
    public function construct(): void
    {
        $this->config = [
            'max_message_length' => 65535,
            'messages_per_page' => 50,
            'allowed_reactions' => ['👍', '👎', '😂', '❤️', '😮', '😢', '😡'],
        ];
    }
    
    /**
     * Get all accessible channels for user
     */
    public function getChannels(User $user)
    {
        // Get all public and announcement channels
        $publicChannels = ChatChannel::where('is_active', true)
            ->where(function ($query) {
                $query->where('type', 'public')
                    ->orWhere('type', 'announcement');
            })
            ->with(['creator', 'members'])
            ->withCount(['messages', 'members'])
            ->get();

        // Get private channels user is a member of
        $memberChannelIds = ChannelMember::where('user_id', $user->id)->pluck('channel_id');
        $privateChannels = ChatChannel::where('is_active', true)
            ->whereIn('id', $memberChannelIds)
            ->whereNotIn('type', ['public', 'announcement'])
            ->with(['creator', 'members'])
            ->withCount(['messages', 'members'])
            ->get();

        return $publicChannels->merge($privateChannels)->unique('id')->values();
    }
    
    /**
     * Ensure user membership in channel
     */
    public function ensureMembership(User $user, ChatChannel $channel): bool
    {
        if ($user->chatChannels()->where('chat_channels.id', $channel->id)->exists()) {
            return true;
        }
        
        if (in_array($channel->type, ['public', 'announcement'])) {
            ChannelMember::create([
                'channel_id' => $channel->id,
                'user_id' => $user->id,
                'role' => 'member',
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Get channel messages
     */
    public function getMessages(User $user, ChatChannel $channel, int $perPage = 50)
    {
        if (!$this->ensureMembership($user, $channel)) {
            throw new \Exception('Unauthorized');
        }

        $messages = $channel->messages()
            ->with(['user', 'replies.user', 'reactions.user'])
            ->latest()
            ->paginate($perPage);

        // Mark as read
        $membership = $user->channelMemberships()
            ->where('channel_id', $channel->id)
            ->first();
        
        if ($membership) {
            $membership->update(['last_read_at' => now()]);
        }

        return $messages;
    }
    
    /**
     * Send a message
     */
    public function sendMessage(User $user, ChatChannel $channel, string $content, ?int $replyToId = null): ChatMessage
    {
        if (!$this->ensureMembership($user, $channel)) {
            throw new \Exception('Unauthorized');
        }
        
        if ($channel->type === 'announcement' && !$user->hasRole('admin')) {
            throw new \Exception('Only admins can post in announcement channels.');
        }
        
        // Apply content filter hook
        $filterResult = $this->applyModuleHook('filterMessageContent', [
            'content' => $content,
            'user' => $user,
            'channel' => $channel,
        ]);
        $content = $filterResult['content'] ?? $content;

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $content,
            'reply_to_id' => $replyToId,
        ]);
        
        $this->applyModuleHook('OnMessageSent', [
            'message' => $message,
            'user' => $user,
            'channel' => $channel,
        ]);

        return $message->load(['user', 'replies.user']);
    }
    
    /**
     * Delete a message
     */
    public function deleteMessage(User $user, ChatMessage $message): bool
    {
        if ($message->user_id !== $user->id && !$user->hasRole('admin')) {
            throw new \Exception('Unauthorized');
        }
        
        $this->applyModuleHook('OnMessageDeleted', [
            'message' => $message,
            'user' => $user,
        ]);

        $message->delete();
        return true;
    }
    
    /**
     * Add reaction to message
     */
    public function addReaction(User $user, ChatMessage $message, string $emoji): MessageReaction
    {
        if (!in_array($emoji, $this->config['allowed_reactions'])) {
            throw new \Exception('Invalid reaction.');
        }
        
        $reaction = MessageReaction::firstOrCreate([
            'message_id' => $message->id,
            'user_id' => $user->id,
            'emoji' => $emoji,
        ]);
        
        $this->applyModuleHook('OnReaction', [
            'message' => $message,
            'user' => $user,
            'emoji' => $emoji,
        ]);

        return $reaction;
    }
    
    /**
     * Remove reaction
     */
    public function removeReaction(User $user, ChatMessage $message, string $emoji): bool
    {
        MessageReaction::where([
            'message_id' => $message->id,
            'user_id' => $user->id,
            'emoji' => $emoji,
        ])->delete();

        return true;
    }
    
    /**
     * Create a new channel
     */
    public function createChannel(User $user, string $name, string $type = 'public', ?string $description = null): ChatChannel
    {
        $channel = ChatChannel::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $description,
            'type' => $type,
            'created_by' => $user->id,
            'is_active' => true,
        ]);
        
        // Creator is admin
        ChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
        
        $this->applyModuleHook('OnChannelCreated', [
            'channel' => $channel,
            'user' => $user,
        ]);

        return $channel;
    }
    
    /**
     * Get unread message count
     */
    public function getUnreadCount(User $user): int
    {
        return ChannelMember::where('user_id', $user->id)
            ->whereHas('channel', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['channel' => function ($query) {
                $query->withCount('messages');
            }])
            ->get()
            ->sum(function ($membership) {
                if (!$membership->last_read_at) {
                    return $membership->channel->messages_count ?? 0;
                }
                return $membership->channel->messages()
                    ->where('created_at', '>', $membership->last_read_at)
                    ->count();
            });
    }
}
