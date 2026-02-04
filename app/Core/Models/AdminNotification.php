<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'icon',
        'link',
        'priority',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Notification types
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_ERROR = 'error';
    const TYPE_TASK = 'task';
    const TYPE_USER = 'user';
    const TYPE_SYSTEM = 'system';
    const TYPE_REPORT = 'report';
    const TYPE_TICKET = 'ticket';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the admin user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if notification has been read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get time since notification was created (human readable).
     */
    public function timeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope to only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Create a notification for all admins.
     */
    public static function notifyAllAdmins(string $type, string $title, string $message, array $data = [], string $icon = '🔔', ?string $link = null, string $priority = self::PRIORITY_NORMAL): void
    {
        $admins = User::role(['admin', 'moderator'])->get();

        foreach ($admins as $admin) {
            self::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'icon' => $icon,
                'link' => $link,
                'priority' => $priority,
            ]);
        }
    }

    /**
     * Create a notification for a specific admin.
     */
    public static function notifyAdmin(int $userId, string $type, string $title, string $message, array $data = [], string $icon = '🔔', ?string $link = null, string $priority = self::PRIORITY_NORMAL): self
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => $icon,
            'link' => $link,
            'priority' => $priority,
        ]);
    }

    /**
     * Get icon based on notification type.
     */
    public static function getDefaultIcon(string $type): string
    {
        return match ($type) {
            self::TYPE_SUCCESS => '✅',
            self::TYPE_WARNING => '⚠️',
            self::TYPE_ERROR => '❌',
            self::TYPE_TASK => '📋',
            self::TYPE_USER => '👤',
            self::TYPE_SYSTEM => '⚙️',
            self::TYPE_REPORT => '📊',
            self::TYPE_TICKET => '🎫',
            default => '🔔',
        };
    }
}
