<?php

namespace App\Plugins\Events\Services;

use App\Plugins\Events\Models\Event;
use App\Plugins\Events\Models\EventParticipant;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class EventService
{
    /**
     * Get events filtered by status
     */
    public function getEvents(string $filter = 'all', int $perPage = 20)
    {
        $query = Event::withCount('participants');

        switch ($filter) {
            case 'active':
                $query->where('status', 'active');
                break;
            case 'upcoming':
                $query->where('status', 'upcoming');
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
        }

        return $query->orderByDesc('start_date')->paginate($perPage);
    }

    /**
     * Get event details with participants
     */
    public function getEvent(int $id, ?User $user = null): array
    {
        $event = Event::with(['participants.user'])
            ->withCount('participants')
            ->findOrFail($id);

        $userParticipation = null;
        if ($user) {
            $userParticipation = EventParticipant::where('event_id', $id)
                ->where('user_id', $user->id)
                ->first();
        }

        return [
            'event' => $event,
            'user_participation' => $userParticipation,
        ];
    }

    /**
     * Join an event
     */
    public function joinEvent(Event $event, User $user): EventParticipant
    {
        if (!in_array($event->status, ['upcoming', 'active'])) {
            throw new \Exception('This event is not currently joinable.');
        }

        if ($event->min_level && $user->level < $event->min_level) {
            throw new \Exception("You must be at least level {$event->min_level} to join.");
        }

        if (EventParticipant::where('event_id', $event->id)->where('user_id', $user->id)->exists()) {
            throw new \Exception('You are already participating in this event.');
        }

        if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
            throw new \Exception('This event is full.');
        }

        return DB::transaction(function () use ($event, $user) {
            if ($event->entry_fee > 0) {
                if ($user->cash < $event->entry_fee) {
                    throw new \Exception('Insufficient funds for the entry fee.');
                }
                $user->decrement('cash', $event->entry_fee);
            }

            return EventParticipant::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'score' => 0,
                'joined_at' => now(),
            ]);
        });
    }

    /**
     * Leave an event
     */
    public function leaveEvent(Event $event, User $user): void
    {
        $participant = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($event->status === 'active') {
            throw new \Exception('Cannot leave an active event.');
        }

        $participant->delete();
    }

    /**
     * Get event leaderboard
     */
    public function getLeaderboard(int $eventId, int $limit = 50)
    {
        return EventParticipant::where('event_id', $eventId)
            ->with('user:id,username,level')
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }

    /**
     * Claim event rewards
     */
    public function claimReward(Event $event, User $user): array
    {
        $participant = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($event->status !== 'completed') {
            throw new \Exception('Event is not yet completed.');
        }

        if ($participant->reward_claimed) {
            throw new \Exception('Reward already claimed.');
        }

        $rewards = $this->calculateRewards($event, $participant);

        DB::transaction(function () use ($user, $participant, $rewards) {
            $user->increment('cash', $rewards['cash']);
            $user->increment('experience', $rewards['experience']);
            $participant->update(['reward_claimed' => true]);
        });

        return $rewards;
    }

    /**
     * Calculate rewards based on rank
     */
    protected function calculateRewards(Event $event, EventParticipant $participant): array
    {
        $rank = $participant->rank ?? 999;
        $baseCash = $event->reward_cash ?? 0;
        $baseXp = $event->reward_experience ?? 0;

        $multiplier = match (true) {
            $rank === 1 => 3.0,
            $rank === 2 => 2.0,
            $rank === 3 => 1.5,
            $rank <= 10 => 1.0,
            default => 0.5,
        };

        return [
            'cash' => (int) ($baseCash * $multiplier),
            'experience' => (int) ($baseXp * $multiplier),
            'rank' => $rank,
        ];
    }

    /**
     * Start an event (admin)
     */
    public function startEvent(Event $event): Event
    {
        if ($event->status !== 'upcoming') {
            throw new \Exception('Only upcoming events can be started.');
        }

        $event->update(['status' => 'active']);
        return $event->fresh();
    }

    /**
     * End an event and calculate rankings (admin)
     */
    public function endEvent(Event $event): Event
    {
        if ($event->status !== 'active') {
            throw new \Exception('Only active events can be ended.');
        }

        $participants = $event->participants()->orderByDesc('score')->get();
        foreach ($participants as $i => $participant) {
            $participant->update(['rank' => $i + 1]);
        }

        $event->update(['status' => 'completed', 'end_date' => now()]);
        return $event->fresh();
    }
}
