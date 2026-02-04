<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'api_key_id',
        'method',
        'endpoint',
        'status_code',
        'response_time',
        'ip_address',
        'user_agent',
        'request_headers',
        'request_body',
        'response_body',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_body' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * API Key relationship
     */
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * Check if request was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }
}
