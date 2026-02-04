<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserTimer extends Model
{
    protected $fillable = [
        'user_id',
        'timer_name',
        'expires_at',
        'duration',
        'metadata'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'duration' => 'integer'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Check if timer is still active (not expired)
     */
    public function isActive(): bool
    {
        return $this->expires_at->isFuture();
    }
    
    /**
     * Check if timer has expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
    
    /**
     * Get remaining time in seconds
     */
    public function remainingSeconds(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return Carbon::now()->diffInSeconds($this->expires_at, false);
    }
    
    /**
     * Get human-readable time remaining
     */
    public function remainingTime(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }
        
        return $this->expires_at->diffForHumans();
    }
}
