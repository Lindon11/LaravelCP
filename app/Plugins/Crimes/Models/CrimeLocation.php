<?php

namespace App\Plugins\Crimes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrimeLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'energy_cost',
        'min_level',
        'success_rate_base',
        'min_cash',
        'max_cash',
        'min_exp',
        'max_exp',
        'respect_reward',
        'jail_chance',
        'cooldown_seconds',
        'difficulty',
        'color_theme',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'energy_cost' => 'integer',
        'min_level' => 'integer',
        'success_rate_base' => 'integer',
        'min_cash' => 'integer',
        'max_cash' => 'integer',
        'min_exp' => 'integer',
        'max_exp' => 'integer',
        'respect_reward' => 'integer',
        'jail_chance' => 'integer',
        'cooldown_seconds' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Scope to get active locations
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by defined order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get locations available for a specific user level
     */
    public function scopeAvailableForLevel($query, int $level)
    {
        return $query->where('min_level', '<=', $level);
    }

    /**
     * Calculate success rate for a user (can be modified by stats later)
     */
    public function calculateSuccessRate(User $user): int
    {
        // Base success rate
        $successRate = $this->success_rate_base;
        
        // TODO: Add modifiers based on user stats, equipment, etc.
        // Example: $successRate += $user->skill_crime * 0.5;
        
        return min(95, max(5, $successRate)); // Cap between 5% and 95%
    }

    /**
     * Get crime attempts for this location
     */
    public function attempts()
    {
        return $this->hasMany(CrimeLocationAttempt::class);
    }

    /**
     * Get user's attempts for this location
     */
    public function userAttempts(User $user)
    {
        return $this->attempts()->where('user_id', $user->id);
    }

    /**
     * Get user's stats for this location
     */
    public function getUserStats(User $user): array
    {
        $attempts = $this->userAttempts($user);
        
        return [
            'total_attempts' => $attempts->count(),
            'successful' => $attempts->where('success', true)->count(),
            'failed' => $attempts->where('success', false)->count(),
            'jailed' => $attempts->where('jailed', true)->count(),
            'total_cash_earned' => $attempts->sum('cash_earned'),
            'total_exp_earned' => $attempts->sum('exp_earned'),
        ];
    }
}
