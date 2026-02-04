<?php

namespace App\Plugins\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    protected $fillable = [
        'channel_id',
        'user_id',
        'reply_to_id',
        'message',
        'is_edited',
        'edited_at',
        'is_pinned',
        'pinned_at',
        'pinned_by',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChatChannel::class, 'channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'reply_to_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class, 'message_id');
    }

    public function pinnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function markAsEdited(): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }

    public function pin(User $user): void
    {
        $this->update([
            'is_pinned' => true,
            'pinned_at' => now(),
            'pinned_by' => $user->id,
        ]);
    }

    public function unpin(): void
    {
        $this->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_by' => null,
        ]);
    }
}
