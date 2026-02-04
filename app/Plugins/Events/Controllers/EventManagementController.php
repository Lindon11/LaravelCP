<?php

namespace App\Plugins\Events\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Events\Models\Event;
use App\Plugins\Events\Models\EventParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventManagementController extends Controller
{
    /**
     * List all events for admin
     */
    public function index(Request $request)
    {
        $events = Event::withCount('participants')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    /**
     * Create a new event
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|in:competition,seasonal,boss_raid,double_xp,double_money,treasure_hunt,pvp_tournament',
            'starts_at' => 'required|date|after:now',
            'ends_at' => 'required|date|after:starts_at',
            'min_level' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:2',
            'entry_fee' => 'nullable|integer|min:0',
            'rewards' => 'nullable|array',
            'rules' => 'nullable|array',
            'banner_image' => 'nullable|string',
            'recurring' => 'nullable|boolean',
            'recurring_pattern' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);
        $validated['status'] = 'scheduled';
        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event created',
            'event' => $event,
        ]);
    }

    /**
     * Update an event
     */
    public function update(Request $request, int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|string|in:competition,seasonal,boss_raid,double_xp,double_money,treasure_hunt,pvp_tournament',
            'status' => 'sometimes|string|in:scheduled,active,completed,cancelled',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date',
            'min_level' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:2',
            'entry_fee' => 'nullable|integer|min:0',
            'rewards' => 'nullable|array',
            'rules' => 'nullable|array',
            'banner_image' => 'nullable|string',
        ]);

        $event->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Event updated',
            'event' => $event->fresh(),
        ]);
    }

    /**
     * Delete an event
     */
    public function destroy(int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        if ($event->status === 'active') {
            return response()->json(['success' => false, 'message' => 'Cannot delete an active event'], 400);
        }

        $event->participants()->delete();
        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted',
        ]);
    }

    /**
     * Start an event
     */
    public function start(int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $event->update([
            'status' => 'active',
            'starts_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event started',
        ]);
    }

    /**
     * End an event and calculate rankings
     */
    public function end(int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        // Calculate rankings
        $participants = EventParticipant::where('event_id', $id)
            ->orderBy('score', 'desc')
            ->get();

        $rank = 1;
        foreach ($participants as $participant) {
            $participant->update(['rank' => $rank]);
            $rank++;
        }

        $event->update([
            'status' => 'completed',
            'ends_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event ended and rankings calculated',
        ]);
    }

    /**
     * Get event participants
     */
    public function participants(int $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }

        $participants = EventParticipant::where('event_id', $id)
            ->with('user:id,username,email,level')
            ->orderBy('score', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'participants' => $participants,
        ]);
    }
}
