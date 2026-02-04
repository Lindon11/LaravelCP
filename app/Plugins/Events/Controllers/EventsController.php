<?php

namespace App\Plugins\Events\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Events\Models\Event;
use App\Plugins\Events\Models\EventParticipant;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    /**
     * List all events
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'active');

        $query = Event::with(['participants' => function($q) {
            $q->limit(5)->orderBy('score', 'desc');
        }]);

        switch ($filter) {
            case 'upcoming':
                $query->upcoming();
                break;
            case 'completed':
                $query->completed()->orderBy('ends_at', 'desc');
                break;
            case 'all':
                $query->orderBy('starts_at', 'desc');
                break;
            default:
                $query->active();
        }

        $events = $query->paginate(10);

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    /**
     * Get single event
     */
    public function show(int $id)
    {
        $event = Event::with(['participants' => function($q) {
            $q->with('user:id,username,level')->orderBy('score', 'desc')->limit(100);
        }])->find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $userParticipation = null;
        if (auth()->check()) {
            $userParticipation = EventParticipant::where('event_id', $id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return response()->json([
            'success' => true,
            'event' => $event,
            'participation' => $userParticipation,
        ]);
    }

    /**
     * Join an event
     */
    public function join(int $id)
    {
        $user = auth()->user();
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        if (!$event->isJoinable()) {
            return response()->json(['success' => false, 'message' => 'Event is not available to join'], 400);
        }

        if ($event->min_level && $user->level < $event->min_level) {
            return response()->json([
                'success' => false,
                'message' => "You need to be level {$event->min_level} to join this event"
            ], 400);
        }

        $existing = EventParticipant::where('event_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already joined this event'], 400);
        }

        if ($event->entry_fee > 0) {
            if ($user->money < $event->entry_fee) {
                return response()->json(['success' => false, 'message' => 'Not enough money for entry fee'], 400);
            }
            $user->decrement('money', $event->entry_fee);
        }

        EventParticipant::create([
            'event_id' => $id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'score' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined event',
        ]);
    }

    /**
     * Leave an event
     */
    public function leave(int $id)
    {
        $participant = EventParticipant::where('event_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Not participating in this event'], 400);
        }

        $event = Event::find($id);
        if ($event && $event->status === 'active') {
            return response()->json(['success' => false, 'message' => 'Cannot leave an active event'], 400);
        }

        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Left the event',
        ]);
    }

    /**
     * Get event leaderboard
     */
    public function leaderboard(int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $leaderboard = EventParticipant::where('event_id', $id)
            ->with('user:id,username,level,avatar')
            ->orderBy('score', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Claim event reward
     */
    public function claimReward(int $id)
    {
        $participant = EventParticipant::where('event_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Not a participant'], 400);
        }

        if ($participant->reward_claimed) {
            return response()->json(['success' => false, 'message' => 'Reward already claimed'], 400);
        }

        $event = Event::find($id);
        if ($event->status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Event not completed yet'], 400);
        }

        if (!$participant->rank || !isset($event->rewards[$participant->rank])) {
            return response()->json(['success' => false, 'message' => 'No reward available for your rank'], 400);
        }

        $reward = $event->rewards[$participant->rank];
        $user = auth()->user();

        // Apply rewards
        if (isset($reward['money'])) {
            $user->increment('money', $reward['money']);
        }
        if (isset($reward['experience'])) {
            $user->increment('experience', $reward['experience']);
        }

        $participant->update([
            'reward_claimed' => true,
            'reward_data' => $reward,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reward claimed!',
            'reward' => $reward,
        ]);
    }
}
