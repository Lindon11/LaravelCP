<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_category_id',
        'category_id',
        'subject',
        'description',
        'message',
        'status',
        'priority',
        'assigned_to',
        'is_read',
        'last_reply_at',
        'closed_at',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    // Accessor to get message (alias for description)
    public function getMessageAttribute()
    {
        return $this->description;
    }

    // Mutator to set message (alias for description)
    public function setMessageAttribute($value)
    {
        $this->attributes['description'] = $value;
    }

    // Accessor to get category_id (alias for ticket_category_id)
    public function getCategoryIdAttribute()
    {
        return $this->ticket_category_id;
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function isOpen(): bool
    {
        return $this->status !== 'closed';
    }

    public function close(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }
}
