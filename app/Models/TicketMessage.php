<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_reply',
        'is_staff',
        'is_admin',
        'is_read',
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean',
        'is_staff' => 'boolean',
        'is_admin' => 'boolean',
        'is_read' => 'boolean',
    ];

    // Accessor to get is_admin (alias for is_staff_reply)
    public function getIsAdminAttribute()
    {
        return $this->is_staff_reply || $this->attributes['is_admin'] ?? false;
    }

    // Mutator to set is_admin (alias for is_staff_reply)
    public function setIsAdminAttribute($value)
    {
        $this->attributes['is_staff_reply'] = $value;
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
