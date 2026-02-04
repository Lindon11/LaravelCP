<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'headers',
        'retry_count',
        'last_triggered_at',
        'last_response_code',
        'failure_count',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'headers' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'secret' => 'encrypted',
        ];
    }

    /**
     * Get the user that owns this webhook
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get webhook delivery logs
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * Check if webhook subscribes to an event
     */
    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? []) || in_array('*', $this->events ?? []);
    }

    /**
     * Increment failure count
     */
    public function incrementFailure(): void
    {
        $this->increment('failure_count');

        // Disable after 10 consecutive failures
        if ($this->failure_count >= 10) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * Reset failure count on success
     */
    public function resetFailure(): void
    {
        $this->update(['failure_count' => 0]);
    }
}
