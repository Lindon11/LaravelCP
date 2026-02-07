<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseKey extends Model
{
    protected $fillable = [
        'license_id',
        'customer',
        'email',
        'domain',
        'tier',
        'expires',
        'max_users',
        'plugins',
        'masked_key',
        'is_activated',
        'activated_domain',
        'activated_ip',
        'activated_at',
        'notes',
        'is_revoked',
        'revoked_at',
    ];

    protected $casts = [
        'is_activated' => 'boolean',
        'activated_at' => 'datetime',
        'is_revoked' => 'boolean',
        'revoked_at' => 'datetime',
        'max_users' => 'integer',
    ];

    /**
     * Scope: active (not revoked).
     */
    public function scopeActive($query)
    {
        return $query->where('is_revoked', false);
    }

    /**
     * Scope: activated keys.
     */
    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    /**
     * Get the status label.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_revoked) return 'revoked';
        if ($this->is_activated) return 'activated';
        return 'pending';
    }
}
