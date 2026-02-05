<?php

namespace App\Plugins\Tickets\Services;

use App\Plugins\Tickets\Models\Ticket;
use App\Plugins\Tickets\Models\TicketReply;
use App\Core\Models\User;
use Illuminate\Support\Facades\DB;

class TicketService
{
    /**
     * Get user's tickets
     */
    public function getUserTickets(User $user, array $filters = [], int $perPage = 20)
    {
        $query = Ticket::where('user_id', $user->id)
            ->with('latestReply');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('updated_at')->paginate($perPage);
    }

    /**
     * Get all tickets (admin)
     */
    public function getAllTickets(array $filters = [], int $perPage = 20)
    {
        $query = Ticket::with(['user:id,username', 'assignee:id,username', 'latestReply']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->orderByRaw("FIELD(status, 'open', 'in_progress', 'waiting', 'resolved', 'closed')")
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    /**
     * Get a single ticket with replies
     */
    public function getTicket(Ticket $ticket, User $user): array
    {
        // Regular users can only see their own tickets
        if (!$user->hasRole('admin') && !$user->hasRole('moderator') && $ticket->user_id !== $user->id) {
            throw new \Exception('You do not have permission to view this ticket.');
        }

        return [
            'ticket' => $ticket->load(['user:id,username', 'assignee:id,username']),
            'replies' => $ticket->replies()
                ->with('user:id,username,avatar')
                ->orderBy('created_at')
                ->get(),
        ];
    }

    /**
     * Create a new support ticket
     */
    public function createTicket(User $user, array $data): Ticket
    {
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => $data['subject'],
            'category' => $data['category'] ?? 'general',
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        // Create the initial message as the first reply
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $data['body'],
            'is_staff' => false,
        ]);

        return $ticket->load('replies');
    }

    /**
     * Reply to a ticket
     */
    public function replyToTicket(Ticket $ticket, User $user, array $data): TicketReply
    {
        $isStaff = $user->hasRole('admin') || $user->hasRole('moderator');

        if (!$isStaff && $ticket->user_id !== $user->id) {
            throw new \Exception('You cannot reply to this ticket.');
        }

        if ($ticket->status === 'closed') {
            throw new \Exception('This ticket is closed.');
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $data['body'],
            'is_staff' => $isStaff,
        ]);

        // Update ticket status
        if ($isStaff) {
            $ticket->update(['status' => 'waiting']);
        } else {
            $ticket->update(['status' => 'open']);
        }

        $ticket->touch();

        return $reply->load('user:id,username,avatar');
    }

    /**
     * Update ticket status (admin)
     */
    public function updateTicketStatus(Ticket $ticket, string $status, ?int $assigneeId = null): Ticket
    {
        $data = ['status' => $status];

        if ($assigneeId !== null) {
            $data['assigned_to'] = $assigneeId;
        }

        if ($status === 'resolved' || $status === 'closed') {
            $data['resolved_at'] = now();
        }

        $ticket->update($data);

        return $ticket->fresh();
    }

    /**
     * Assign ticket to a staff member
     */
    public function assignTicket(Ticket $ticket, int $staffId): Ticket
    {
        $ticket->update([
            'assigned_to' => $staffId,
            'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        return $ticket->fresh(['assignee:id,username']);
    }

    /**
     * Close a ticket
     */
    public function closeTicket(Ticket $ticket, User $user): void
    {
        $isStaff = $user->hasRole('admin') || $user->hasRole('moderator');

        if (!$isStaff && $ticket->user_id !== $user->id) {
            throw new \Exception('You cannot close this ticket.');
        }

        $ticket->update([
            'status' => 'closed',
            'resolved_at' => $ticket->resolved_at ?? now(),
        ]);
    }

    /**
     * Get ticket statistics (admin)
     */
    public function getStats(): array
    {
        return [
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'waiting' => Ticket::where('status', 'waiting')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'closed_today' => Ticket::where('status', 'closed')
                ->whereDate('resolved_at', today())
                ->count(),
            'avg_resolution_hours' => (int) Ticket::whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
        ];
    }
}
