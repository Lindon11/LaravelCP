<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffChatReadStatus extends Model
{
    protected $table = 'staff_chat_read_status';

    protected $fillable = [
        'user_id',
        'last_read_message_id',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last read message.
     */
    public function lastReadMessage(): BelongsTo
    {
        return $this->belongsTo(StaffChatMessage::class, 'last_read_message_id');
    }
}
