<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    /**
     * Get all tickets for the authenticated user
     */
    public function index(Request $request)
    {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->with(['category', 'assignedUser', 'messages'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($tickets);
    }

    /**
     * Get ticket categories
     */
    public function categories()
    {
        $categories = TicketCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return response()->json($categories);
    }

    /**
     * Create a new ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            'user_id' => $request->user()->id,
            'ticket_category_id' => $validated['ticket_category_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'status' => 'open',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket->load(['category'])
        ], 201);
    }

    /**
     * Get a specific ticket with messages
     */
    public function show(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->with(['category', 'assignedUser', 'messages.user'])
            ->findOrFail($id);
        
        // Mark messages as read
        $ticket->messages()
            ->where('user_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return response()->json($ticket);
    }

    /**
     * Reply to a ticket
     */
    public function reply(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->findOrFail($id);
        
        // Don't allow replies to closed tickets
        if ($ticket->status === 'closed') {
            return response()->json([
                'message' => 'Cannot reply to a closed ticket'
            ], 400);
        }
        
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_staff' => false,
        ]);

        // Update ticket status if it was resolved
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json([
            'message' => 'Reply added successfully',
            'ticket_message' => $message->load('user')
        ], 201);
    }

    /**
     * Close a ticket
     */
    public function close(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->findOrFail($id);
        
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);

        return response()->json([
            'message' => 'Ticket closed successfully',
            'ticket' => $ticket
        ]);
    }

    /**
     * Get unread message count for user's tickets
     */
    public function unreadCount(Request $request)
    {
        $count = TicketMessage::whereHas('ticket', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
        ->where('user_id', '!=', $request->user()->id)
        ->where('is_read', false)
        ->count();

        return response()->json(['count' => $count]);
    }
}
