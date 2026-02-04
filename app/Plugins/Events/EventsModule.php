<?php

namespace App\Plugins\Events;

use App\Plugins\Plugin;
use App\Plugins\Events\Models\Event;
use App\Plugins\Events\Models\EventParticipant;

/**
 * Events Module
 *
 * Handles scheduled server-wide events, competitions, and seasonal content
 */
class EventsModule extends Plugin
{
    protected string $name = 'Events';

    public function construct(): void
    {
        $this->config = [
            'max_concurrent_events' => 3,
            'min_participants' => 2,
            'notification_before' => 3600, // 1 hour before
            'types' => [
                'competition' => 'Competition',
                'seasonal' => 'Seasonal Event',
                'boss_raid' => 'Boss Raid',
                'double_xp' => 'Double XP',
                'double_money' => 'Double Money',
                'treasure_hunt' => 'Treasure Hunt',
                'pvp_tournament' => 'PvP Tournament',
            ],
        ];
    }

    /**
     * Get active events
     */
    public function getActiveEvents(): array
    {
        return Event::where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->orderBy('ends_at')
            ->get()
            ->toArray();
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents(int $limit = 5): array
    {
        return Event::where('status', 'scheduled')
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get past events
     */
    public function getPastEvents(int $limit = 10): array
    {
        return Event::where('status', 'completed')
            ->orderBy('ends_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Join an event
     */
    public function joinEvent(int $userId, int $eventId): array
    {
        $event = Event::find($eventId);

        if (!$event) {
            return ['success' => false, 'message' => 'Event not found'];
        }

        if ($event->status !== 'active' && $event->status !== 'scheduled') {
            return ['success' => false, 'message' => 'Event is not available to join'];
        }

        if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
            return ['success' => false, 'message' => 'Event is full'];
        }

        $existing = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'Already joined this event'];
        }

        EventParticipant::create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'joined_at' => now(),
            'score' => 0,
        ]);

        return ['success' => true, 'message' => 'Successfully joined event'];
    }

    /**
     * Update participant score
     */
    public function updateScore(int $userId, int $eventId, int $points): bool
    {
        $participant = EventParticipant::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if (!$participant) {
            return false;
        }

        $participant->increment('score', $points);
        return true;
    }

    /**
     * Get event leaderboard
     */
    public function getLeaderboard(int $eventId, int $limit = 10): array
    {
        return EventParticipant::where('event_id', $eventId)
            ->with('user:id,username,level')
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get module stats
     */
    public function getStats(?\App\Core\Models\User $user = null): array
    {
        $activeCount = Event::where('status', 'active')->count();
        $upcomingCount = Event::where('status', 'scheduled')->count();

        $participated = 0;
        $rewards = 0;

        if ($user) {
            $participated = EventParticipant::where('user_id', $user->id)->count();
            $rewards = EventParticipant::where('user_id', $user->id)
                ->where('reward_claimed', true)
                ->count();
        }

        return [
            'active_events' => $activeCount,
            'upcoming_events' => $upcomingCount,
            'participated' => $participated,
            'rewards_claimed' => $rewards,
        ];
    }
}
