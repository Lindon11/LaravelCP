<?php

namespace App\Plugins\Chat\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Chat\Models\ChatChannel;
use App\Plugins\Chat\Models\ChannelMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all public and announcement channels that are active
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

        // Merge and return
        $channels = $publicChannels->merge($privateChannels)->unique('id');

        return response()->json($channels->values());
    }

    /**
     * Get unread message count across all channels
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        $unreadCount = ChannelMember::where('user_id', $user->id)
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

        return response()->json(['unread_count' => $unreadCount]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admins and staff can create channels
        if (!$request->user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json(['message' => 'Only admins can create channels'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:public,private,group,announcement',
            'max_members' => 'nullable|integer|min:2',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = $request->user()->id;

        $channel = ChatChannel::create($validated);
        
        // Add creator as owner
        ChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $request->user()->id,
            'role' => 'owner',
        ]);

        return response()->json($channel->load('creator'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, ChatChannel $channel)
    {
        // Check if user is member or channel is public
        if (!$channel->type === 'public' && !$request->user()->chatChannels()->where('chat_channels.id', $channel->id)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            $channel->load(['creator', 'members', 'messages' => fn($q) => $q->latest()->limit(50)])
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChatChannel $channel)
    {
        // Check if user is owner or admin
        $membership = $request->user()->channelMemberships()
            ->where('channel_id', $channel->id)
            ->first();

        if (!$membership || !in_array($membership->role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:public,private,group,announcement',
            'max_members' => 'nullable|integer|min:2',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $channel->update($validated);

        return response()->json($channel->load('creator'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ChatChannel $channel)
    {
        // Only owner can delete
        $membership = $request->user()->channelMemberships()
            ->where('channel_id', $channel->id)
            ->first();

        if (!$membership || $membership->role !== 'owner') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $channel->delete();

        return response()->json(['message' => 'Channel deleted']);
    }

    /**
     * Add user to channel
     */
    public function addMember(Request $request, ChatChannel $channel)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if (ChannelMember::where('channel_id', $channel->id)->where('user_id', $validated['user_id'])->exists()) {
            return response()->json(['message' => 'User already in channel'], 409);
        }

        if ($channel->max_members && $channel->members()->count() >= $channel->max_members) {
            return response()->json(['message' => 'Channel is full'], 400);
        }

        ChannelMember::create([
            'channel_id' => $channel->id,
            'user_id' => $validated['user_id'],
            'role' => 'member',
        ]);

        return response()->json(['message' => 'User added to channel'], 201);
    }

    /**
     * Remove user from channel
     */
    public function removeMember(Request $request, ChatChannel $channel, int $userId)
    {
        $membership = ChannelMember::where('channel_id', $channel->id)
            ->where('user_id', $userId)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'User not in channel'], 404);
        }

        $membership->delete();

        return response()->json(['message' => 'User removed from channel']);
    }
}
