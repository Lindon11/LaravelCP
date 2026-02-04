<?php

namespace App\Plugins\Events\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Core\Models\User;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'min_level',
        'max_participants',
        'entry_fee',
        'rewards',
        'rules',
        'banner_image',
        'created_by',
        'recurring',
        'recurring_pattern',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'rewards' => 'array',
        'rules' => 'array',
        'metadata' => 'array',
        'recurring' => 'boolean',
        'entry_fee' => 'integer',
        'min_level' => 'integer',
        'max_participants' => 'integer',
    ];

    /**
     * Event participants
     */
    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Event creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active events
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
            ->where('starts_at', '>', now());
    }

    /**
     * Scope for completed events
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if event is joinable
     */
    public function isJoinable(): bool
    {
        if (!in_array($this->status, ['active', 'scheduled'])) {
            return false;
        }

        if ($this->max_participants && $this->participants()->count() >= $this->max_participants) {
            return false;
        }

        return true;
    }

    /**
     * Get top participants
     */
    public function getTopParticipants(int $limit = 10)
    {
        return $this->participants()
            ->with('user:id,username,level')
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get();
    }
}
