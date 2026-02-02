<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index()
    {
        $player = auth()->user();
        
        $tickets = Ticket::where('user_id', $player->id)
            ->with(['category', 'messages'])
            ->withCount('messages')
            ->latest()
            ->get();

        $categories = TicketCategory::where('active', true)
            ->orderBy('order')
            ->get();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'categories' => $categories,
        ]);
    }

    public function show(Ticket $ticket)
    {
        $player = auth()->user();
        
        // Check if ticket belongs to player
        if ($ticket->user_id !== $player->id) {
            abort(403);
        }

        $ticket->load(['category', 'messages.user', 'assignedTo']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function store(Request $request)
    {
        $player = auth()->user();

        $validated = $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            'user_id' => $player->id,
            'ticket_category_id' => $validated['ticket_category_id'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket created successfully! Our team will respond soon.');
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $player = auth()->user();
        
        // Check if ticket belongs to player
        if ($ticket->user_id !== $player->id) {
            abort(403);
        }

        if (!$ticket->isOpen()) {
            return back()->with('error', 'This ticket is closed.');
        }

        $validated = $request->validate([
            'message' => 'required|string|min:5',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_staff_reply' => false,
        ]);

        $ticket->update([
            'status' => 'waiting_response',
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'Reply added successfully!');
    }

    public function close(Ticket $ticket)
    {
        $player = auth()->user();
        
        // Check if ticket belongs to player
        if ($ticket->user_id !== $player->id) {
            abort(403);
        }

        $ticket->close();

        return back()->with('success', 'Ticket closed successfully!');
    }
}

