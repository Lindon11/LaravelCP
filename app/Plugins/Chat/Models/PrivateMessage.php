<?php

namespace App\Plugins\Chat\Models;

use App\Core\Models\User;
use App\Core\Facades\TextFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'read_at',
        'sender_deleted',
        'recipient_deleted',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sender_deleted' => 'boolean',
        'recipient_deleted' => 'boolean',
    ];

    protected $appends = ['formatted_body'];

    /**
     * Get the formatted body with BBCode and emojis parsed
     */
    public function getFormattedBodyAttribute(): string
    {
        return TextFormatter::format($this->body ?? '');
    }

    /**
     * Get the sender.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the recipient.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Check if message is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for inbox messages (not deleted by recipient).
     */
    public function scopeInbox($query, int $userId)
    {
        return $query->where('recipient_id', $userId)
            ->where('recipient_deleted', false);
    }

    /**
     * Scope for sent messages (not deleted by sender).
     */
    public function scopeSent($query, int $userId)
    {
        return $query->where('sender_id', $userId)
            ->where('sender_deleted', false);
    }
}
