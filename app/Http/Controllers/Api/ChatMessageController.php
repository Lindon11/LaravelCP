<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChannelMember;
use App\Models\MessageReaction;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    /**
     * Auto-join user to public/announcement channels
     */
    private function ensureMembership(Request $request, ChatChannel $channel): bool
    {
        $user = $request->user();
        
        // Check if already a member
        if ($user->chatChannels()->where('chat_channels.id', $channel->id)->exists()) {
            return true;
        }
        
        // Auto-join public and announcement channels
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
     * Get messages for a channel
     */
    public function index(Request $request, ChatChannel $channel)
    {
        // Ensure user is member (auto-join for public channels)
        if (!$this->ensureMembership($request, $channel)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $channel->messages()
            ->with(['user', 'replies.user', 'reactions.user'])
            ->latest()
            ->paginate(50);

        // Mark as read
        $membership = $request->user()->channelMemberships()
            ->where('channel_id', $channel->id)
            ->first();
        
        if ($membership) {
            $membership->update(['last_read_at' => now()]);
        }

        return response()->json($messages);
    }

    /**
     * Send a message
     */
    public function store(Request $request, ChatChannel $channel)
    {
        // Ensure user is member (auto-join for public channels)
        if (!$this->ensureMembership($request, $channel)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:65535',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);

        // Sanitize message to prevent XSS (strip HTML tags, keep safe content)
        $sanitizedMessage = strip_tags($validated['message']);

        $message = ChatMessage::create([
            'channel_id' => $channel->id,
            'user_id' => $request->user()->id,
            'reply_to_id' => $validated['reply_to_id'] ?? null,
            'message' => $sanitizedMessage,
        ]);

        return response()->json($message->load(['user', 'replies.user', 'reactions.user']), 201);
    }

    /**
     * Update a message
     */
    public function update(Request $request, ChatMessage $message)
    {
        // Only message author can edit
        if ($message->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:65535',
        ]);

        $message->markAsEdited();
        $message->update($validated);

        return response()->json($message->load(['user', 'replies.user', 'reactions.user']));
    }

    /**
     * Delete a message
     */
    public function destroy(Request $request, ChatMessage $message)
    {
        // Only author or channel admin can delete
        $isAuthor = $message->user_id === $request->user()->id;
        $isAdmin = $request->user()->channelMemberships()
            ->where('channel_id', $message->channel_id)
            ->where(function ($q) {
                $q->where('role', 'owner')->orWhere('role', 'admin');
            })
            ->exists();

        if (!$isAuthor && !$isAdmin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }

    /**
     * Add a reaction to a message
     */
    public function addReaction(Request $request, ChatMessage $message)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        // Check if reaction already exists
        $reaction = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $request->user()->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($reaction) {
            $reaction->delete();
            return response()->json(['message' => 'Reaction removed']);
        }

        MessageReaction::create([
            'message_id' => $message->id,
            'user_id' => $request->user()->id,
            'emoji' => $validated['emoji'],
        ]);

        return response()->json(['message' => 'Reaction added'], 201);
    }

    /**
     * Get reactions for a message
     */
    public function reactions(ChatMessage $message)
    {
        $reactions = $message->reactions()
            ->with('user')
            ->get()
            ->groupBy('emoji')
            ->map(function ($group) {
                return [
                    'emoji' => $group[0]->emoji,
                    'count' => $group->count(),
                    'users' => $group->pluck('user.name')->toArray(),
                ];
            });

        return response()->json($reactions);
    }

    /**
     * Pin a message
     */
    public function pin(Request $request, ChatMessage $message)
    {
        // Only channel admins can pin messages
        $isAdmin = $request->user()->channelMemberships()
            ->where('channel_id', $message->channel_id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (!$isAdmin && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->pin($request->user());

        return response()->json($message->load(['user', 'pinnedBy']));
    }

    /**
     * Unpin a message
     */
    public function unpin(Request $request, ChatMessage $message)
    {
        // Only channel admins can unpin messages
        $isAdmin = $request->user()->channelMemberships()
            ->where('channel_id', $message->channel_id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();

        if (!$isAdmin && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $message->unpin();

        return response()->json($message->load(['user']));
    }

    /**
     * Get pinned messages for a channel
     */
    public function pinnedMessages(ChatChannel $channel)
    {
        $pinned = $channel->messages()
            ->where('is_pinned', true)
            ->with(['user', 'pinnedBy'])
            ->orderBy('pinned_at', 'desc')
            ->get();

        return response()->json($pinned);
    }
}
