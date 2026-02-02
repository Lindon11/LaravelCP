<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
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
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $tickets = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return response()->json($tickets);
    }

    /**
     * Display the specified ticket with messages.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'category', 'assignedUser', 'messages.user'])
            ->findOrFail($id);
        
        return response()->json($ticket);
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
            'is_staff_reply' => true,
        ]);

        // Update ticket status and last reply time
        $ticket->update([
            'last_reply_at' => now(),
            'status' => $ticket->status === 'closed' ? 'open' : $ticket->status,
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
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
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
     * Assign ticket to a staff member.
     */
    public function assign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket->load('assignedUser')
        ]);
    }
}
