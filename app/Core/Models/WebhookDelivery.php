<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'response_time_ms',
        'attempt',
        'delivered_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the webhook this delivery belongs to
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Check if delivery was successful
     */
    public function isSuccessful(): bool
    {
        return $this->response_code >= 200 && $this->response_code < 300;
    }
}
