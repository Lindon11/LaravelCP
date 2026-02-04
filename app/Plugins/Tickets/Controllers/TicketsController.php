<?php

namespace App\Plugins\Tickets\Controllers;

use App\Core\Http\Controllers\Controller;
use App\Plugins\Tickets\TicketsModule;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    protected TicketsModule $module;
    
    public function __construct()
    {
        $this->module = new TicketsModule();
    }
    
    /**
     * Get user's tickets
     */
    public function index(Request $request)
    {
        $tickets = $this->module->getUserTickets($request->user());
        return response()->json($tickets);
    }
    
    /**
     * Get categories
     */
    public function categories()
    {
        $categories = $this->module->getCategories();
        return response()->json($categories);
    }
    
    /**
     * Create ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);
        
        try {
            $ticket = $this->module->createTicket(
                $request->user(),
                $request->ticket_category_id,
                $request->subject,
                $request->description,
                $request->priority ?? 'medium'
            );
            
            return response()->json([
                'message' => 'Ticket created successfully',
                'ticket' => $ticket
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Show ticket
     */
    public function show(Request $request, $id)
    {
        try {
            $ticket = $this->module->getTicket($request->user(), $id);
            return response()->json($ticket);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
    }
    
    /**
     * Reply to ticket
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:10000',
        ]);
        
        try {
            $message = $this->module->replyToTicket(
                $request->user(),
                $id,
                $request->message
            );
            
            return response()->json([
                'message' => 'Reply sent successfully',
                'ticket_message' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Close ticket
     */
    public function close(Request $request, $id)
    {
        try {
            $ticket = $this->module->closeTicket($request->user(), $id);
            return response()->json([
                'message' => 'Ticket closed',
                'ticket' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Reopen ticket
     */
    public function reopen(Request $request, $id)
    {
        try {
            $ticket = $this->module->reopenTicket($request->user(), $id);
            return response()->json([
                'message' => 'Ticket reopened',
                'ticket' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = $this->module->getUnreadCount($request->user());
        return response()->json(['unread_count' => $count]);
    }
}
