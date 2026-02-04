<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthProvider extends Model
{
    protected $table = 'oauth_providers';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'token',
        'refresh_token',
        'expires_at',
        'avatar',
        'nickname',
    ];

    protected $hidden = [
        'token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    /**
     * Get the user that owns this OAuth provider link
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}
