<?php

namespace App\Plugins\Tickets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tickets\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['player', 'category', 'assignedUser'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ticket_category_id' => 'nullable|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::create($validated);
        return response()->json($ticket->load(['player', 'category', 'assignedUser']), 201);
    }

    public function show(Ticket $ticket)
    {
        return response()->json($ticket->load(['player', 'category', 'assignedUser']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ticket_category_id' => 'nullable|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:open,in_progress,pending,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($validated);
        return response()->json($ticket->load(['player', 'category', 'assignedUser']));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json(null, 204);
    }
}
