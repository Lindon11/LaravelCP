<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'created_by',
        'title',
        'message',
        'type',
        'target',
        'min_level',
        'max_level',
        'location_id',
        'published_at',
        'expires_at',
        'is_active',
        'is_sticky',
        'views',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_sticky' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function isVisibleTo(User $player): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->target === 'level_range') {
            if ($this->min_level && $player->level < $this->min_level) {
                return false;
            }
            if ($this->max_level && $player->level > $this->max_level) {
                return false;
            }
        }

        if ($this->target === 'location' && $this->location_id) {
            if ($player->location_id !== $this->location_id) {
                return false;
            }
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
