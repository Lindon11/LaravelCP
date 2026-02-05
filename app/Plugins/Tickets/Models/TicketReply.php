<?php

namespace App\Plugins\Tickets\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'body',
        'is_staff',
    ];

    protected $casts = [
        'is_staff' => 'boolean',
    ];

    /**
     * The ticket this reply belongs to
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The user who wrote the reply
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
