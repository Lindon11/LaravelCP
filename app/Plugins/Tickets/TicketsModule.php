<?php

namespace App\Plugins\Tickets;

use App\Plugins\Plugin;
use App\Core\Models\User;
use App\Plugins\Tickets\Models\Ticket;
use App\Plugins\Tickets\Models\TicketMessage;
use App\Plugins\Tickets\Models\TicketCategory;

/**
 * Tickets Module
 * 
 * Handles support ticket system
 */
class TicketsModule extends Plugin
{
    protected string $name = 'Tickets';
    
    public function construct(): void
    {
        $this->config = [
            'max_subject_length' => 255,
            'max_description_length' => 10000,
            'auto_close_days' => 7, // Auto-close inactive tickets
        ];
    }
    
    /**
     * Get user's tickets
     */
    public function getUserTickets(User $user)
    {
        return Ticket::where('user_id', $user->id)
            ->with(['category', 'assignedUser', 'messages'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get active categories
     */
    public function getCategories()
    {
        return TicketCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Create a ticket
     */
    public function createTicket(User $user, int $categoryId, string $subject, string $description, string $priority = 'medium'): Ticket
    {
        // Sanitize inputs
        $subject = strip_tags($subject);
        $description = strip_tags($description);
        
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'ticket_category_id' => $categoryId,
            'subject' => $subject,
            'description' => $description,
            'status' => 'open',
            'priority' => $priority,
        ]);
        
        $this->applyModuleHook('OnTicketCreated', [
            'ticket' => $ticket,
            'user' => $user,
        ]);

        return $ticket->load(['category']);
    }
    
    /**
     * Get a ticket with messages
     */
    public function getTicket(User $user, int $ticketId): Ticket
    {
        $ticket = Ticket::where('user_id', $user->id)
            ->with(['category', 'assignedUser', 'messages.user'])
            ->findOrFail($ticketId);
        
        // Mark messages as read
        $ticket->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return $ticket;
    }
    
    /**
     * Reply to a ticket
     */
    public function replyToTicket(User $user, int $ticketId, string $message): TicketMessage
    {
        $ticket = Ticket::where('user_id', $user->id)->findOrFail($ticketId);
        
        if ($ticket->status === 'closed') {
            throw new \Exception('Cannot reply to a closed ticket.');
        }
        
        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => strip_tags($message),
            'is_staff_reply' => false,
        ]);
        
        // Update ticket status to waiting if it was answered
        if ($ticket->status === 'answered') {
            $ticket->update(['status' => 'waiting']);
        }
        
        $this->applyModuleHook('OnTicketReplied', [
            'ticket' => $ticket,
            'message' => $ticketMessage,
            'user' => $user,
        ]);

        return $ticketMessage->load('user');
    }
    
    /**
     * Close a ticket
     */
    public function closeTicket(User $user, int $ticketId): Ticket
    {
        $ticket = Ticket::where('user_id', $user->id)->findOrFail($ticketId);
        
        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
        
        $this->applyModuleHook('OnTicketClosed', [
            'ticket' => $ticket,
            'user' => $user,
            'closed_by' => 'user',
        ]);

        return $ticket;
    }
    
    /**
     * Reopen a ticket
     */
    public function reopenTicket(User $user, int $ticketId): Ticket
    {
        $ticket = Ticket::where('user_id', $user->id)->findOrFail($ticketId);
        
        if ($ticket->status !== 'closed') {
            throw new \Exception('Ticket is not closed.');
        }
        
        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        return $ticket;
    }
    
    /**
     * Get unread ticket count for user
     */
    public function getUnreadCount(User $user): int
    {
        return Ticket::where('user_id', $user->id)
            ->whereHas('messages', function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                    ->where('is_read', false);
            })
            ->count();
    }
    
    // ==================== STAFF METHODS ====================
    
    /**
     * Get all tickets (staff)
     */
    public function getAllTickets(array $filters = [])
    {
        $query = Ticket::with(['user', 'category', 'assignedUser'])
            ->withCount('messages');
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['category_id'])) {
            $query->where('ticket_category_id', $filters['category_id']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }
    
    /**
     * Assign ticket to staff (staff)
     */
    public function assignTicket(User $staff, int $ticketId, ?int $assignToId = null): Ticket
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        $ticket->update([
            'assigned_to' => $assignToId ?? $staff->id,
        ]);
        
        $this->applyModuleHook('OnTicketAssigned', [
            'ticket' => $ticket,
            'assigned_by' => $staff,
            'assigned_to' => $assignToId ?? $staff->id,
        ]);

        return $ticket;
    }
    
    /**
     * Staff reply to ticket
     */
    public function staffReply(User $staff, int $ticketId, string $message): TicketMessage
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $staff->id,
            'message' => strip_tags($message),
            'is_staff_reply' => true,
        ]);
        
        $ticket->update(['status' => 'answered']);
        
        $this->applyModuleHook('OnTicketReplied', [
            'ticket' => $ticket,
            'message' => $ticketMessage,
            'user' => $staff,
            'is_staff' => true,
        ]);

        return $ticketMessage->load('user');
    }
}
