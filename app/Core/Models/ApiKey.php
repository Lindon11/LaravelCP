<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'secret',
        'description',
        'permissions',
        'allowed_ips',
        'allowed_domains',
        'rate_limit',
        'daily_limit',
        'is_active',
        'last_used_at',
        'total_requests',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array',
        'allowed_ips' => 'array',
        'allowed_domains' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'secret',
    ];

    /**
     * Generate a new API key
     */
    public static function generateKey(): string
    {
        return 'lcp_' . Str::random(32);
    }

    /**
     * Generate a new API secret
     */
    public static function generateSecret(): string
    {
        return Str::random(64);
    }

    /**
     * Check if the API key is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Check if domain is allowed
     */
    public function isDomainAllowed(?string $domain): bool
    {
        if (empty($this->allowed_domains)) {
            return true;
        }

        if (!$domain) {
            return false;
        }

        foreach ($this->allowed_domains as $allowedDomain) {
            if (Str::endsWith($domain, $allowedDomain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // No restrictions = full access
        }

        return in_array($permission, $this->permissions) || in_array('*', $this->permissions);
    }

    /**
     * Record API usage
     */
    public function recordUsage(): void
    {
        $this->increment('total_requests');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Creator relationship
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Request logs relationship
     */
    public function requestLogs(): HasMany
    {
        return $this->hasMany(ApiRequestLog::class);
    }

    /**
     * Get requests today count
     */
    public function getRequestsTodayAttribute(): int
    {
        return $this->requestLogs()
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Check if daily limit exceeded
     */
    public function isDailyLimitExceeded(): bool
    {
        if (!$this->daily_limit) {
            return false;
        }

        return $this->requests_today >= $this->daily_limit;
    }
}
