<?php

namespace App\Plugins\Tickets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tickets\Models\Ticket;
use App\Plugins\Tickets\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketManagementController extends Controller
{
    /**
     * Display a listing of all tickets.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'assignedUser'])
            ->withCount('messages');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by assigned staff member (specific user ID)
        if ($request->filled('user_id') && is_numeric($request->user_id)) {
            $query->where('assigned_to', $request->user_id);
        }

        // Filter by assignment status
        if ($request->filled('assigned')) {
            if ($request->assigned === 'unassigned') {
                $query->whereNull('assigned_to');
            } elseif ($request->assigned === 'me') {
                $query->where('assigned_to', $request->user()->id);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Priority ordering (urgent first)
        $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy($sortBy, $sortDir);

        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

        // Get stats
        $stats = [
            'open' => Ticket::where('status', 'open')->count(),
            'waiting' => Ticket::where('status', 'waiting_response')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereNotIn('status', ['closed'])->count(),
        ];

        return response()->json([
            'tickets' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Display the specified ticket with messages.
     */
    public function show($id)
    {
        $ticket = Ticket::with([
            'user',
            'category',
            'assignedUser',
            'messages' => function($q) {
                $q->with('user')->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        return response()->json([
            'ticket' => $ticket
        ]);
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_admin' => true,
        ]);

        // Update ticket
        $ticket->update([
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Reply added successfully',
            'ticket_message' => $message->load('user')
        ], 201);
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,waiting_response,answered,closed',
        ]);

        $ticket->update([
            'status' => $validated['status'],
            'closed_at' => $validated['status'] === 'closed' ? now() : null,
        ]);

        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Update ticket details (priority, etc).
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'category_id' => 'sometimes|exists:ticket_categories,id',
            'subject' => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket->fresh(['user', 'category', 'assignedUser'])
        ]);
    }

    /**
     * Assign ticket to a staff member.
     */
    public function assign(Request $request, $id)
    {
        $ticket = Ticket::with('user')->findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        // Send notification to the assigned admin
        if ($validated['assigned_to'] && $validated['assigned_to'] != $oldAssignee) {
            $notificationService = app(\App\Core\Services\AdminNotificationService::class);
            $notificationService->notify(
                $validated['assigned_to'],
                'ticket',
                'Ticket Assigned to You',
                "Ticket #{$ticket->id}: {$ticket->subject} has been assigned to you.",
                [
                    'ticket_id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'user' => $ticket->user?->username,
                    'priority' => $ticket->priority,
                ],
                '🎫',
                "/tickets/{$ticket->id}",
                $ticket->priority === 'urgent' ? 'high' : 'normal'
            );
        }

        return response()->json([
            'message' => $validated['assigned_to'] ? 'Ticket assigned successfully' : 'Ticket unassigned',
            'ticket' => $ticket->fresh(['user', 'category', 'assignedUser'])
        ]);
    }

    /**
     * Get staff users who can be assigned tickets.
     */
    public function staffUsers(Request $request)
    {
        return $this->getStaffUsers($request);
    }

    /**
     * Get staff users who can be assigned tickets.
     */
    public function getStaffUsers(Request $request)
    {
        $search = $request->get('search', '');

        // Get users with 'manage tickets' permission
        $users = \App\Core\Models\User::permission('manage tickets')
            ->when($search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->select('id', 'username', 'email')
            ->orderBy('username')
            ->limit(20)
            ->get();

        return response()->json([
            'users' => $users
        ]);
    }

    /**
     * Delete a ticket.
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        // Delete all messages first
        $ticket->messages()->delete();

        // Delete the ticket
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }
}
