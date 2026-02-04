<?php

namespace App\Plugins\Chat\Models;

use App\Core\Models\User;
use App\Core\Facades\TextFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectMessage extends Model
{
    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = ['formatted_message'];

    /**
     * Get the formatted message with BBCode and emojis parsed
     */
    public function getFormattedMessageAttribute(): string
    {
        return TextFormatter::format($this->message ?? '');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeBetweenUsers($query, int $user1, int $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('from_user_id', $user1)->where('to_user_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('from_user_id', $user2)->where('to_user_id', $user1);
        });
    }
}
