<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTimer;
use Carbon\Carbon;

class TimerService
{
    /**
     * Set a timer for a user
     */
    public function setTimer(User $user, string $timerName, int $seconds, ?array $metadata = null): UserTimer
    {
        return UserTimer::updateOrCreate(
            [
                'user_id' => $user->id,
                'timer_name' => $timerName
            ],
            [
                'expires_at' => Carbon::now()->addSeconds($seconds),
                'duration' => $seconds,
                'metadata' => $metadata
            ]
        );
    }
    
    /**
     * Check if a timer is active for a user
     */
    public function hasActiveTimer(User $user, string $timerName): bool
    {
        $timer = UserTimer::where('user_id', $user->id)
            ->where('timer_name', $timerName)
            ->first();
            
        if (!$timer) {
            return false;
        }
        
        if ($timer->isExpired()) {
            $timer->delete();
            return false;
        }
        
        return true;
    }
    
    /**
     * Get a timer for a user
     */
    public function getTimer(User $user, string $timerName): ?UserTimer
    {
        $timer = UserTimer::where('user_id', $user->id)
            ->where('timer_name', $timerName)
            ->first();
            
        if ($timer && $timer->isExpired()) {
            $timer->delete();
            return null;
        }
        
        return $timer;
    }
    
    /**
     * Get all active timers for a user
     */
    public function getActiveTimers(User $user): array
    {
        $timers = UserTimer::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now())
            ->get();
            
        return $timers->map(function ($timer) {
            return [
                'name' => $timer->timer_name,
                'expires_at' => $timer->expires_at,
                'remaining_seconds' => $timer->remainingSeconds(),
                'remaining_time' => $timer->remainingTime(),
                'metadata' => $timer->metadata
            ];
        })->toArray();
    }
    
    /**
     * Remove a timer
     */
    public function removeTimer(User $user, string $timerName): bool
    {
        return UserTimer::where('user_id', $user->id)
            ->where('timer_name', $timerName)
            ->delete() > 0;
    }
    
    /**
     * Clear all expired timers for a user
     */
    public function clearExpiredTimers(User $user): int
    {
        return UserTimer::where('user_id', $user->id)
            ->where('expires_at', '<=', Carbon::now())
            ->delete();
    }
    
    /**
     * Clear all timers for a user
     */
    public function clearAllTimers(User $user): int
    {
        return UserTimer::where('user_id', $user->id)->delete();
    }
    
    /**
     * Get remaining time in seconds for a timer
     */
    public function getRemainingSeconds(User $user, string $timerName): int
    {
        $timer = $this->getTimer($user, $timerName);
        
        return $timer ? $timer->remainingSeconds() : 0;
    }
    
    /**
     * Extend a timer by additional seconds
     */
    public function extendTimer(User $user, string $timerName, int $additionalSeconds): ?UserTimer
    {
        $timer = $this->getTimer($user, $timerName);
        
        if (!$timer) {
            return null;
        }
        
        $timer->expires_at = $timer->expires_at->addSeconds($additionalSeconds);
        $timer->duration += $additionalSeconds;
        $timer->save();
        
        return $timer;
    }
    
    /**
     * Reduce a timer by seconds (for early release, etc)
     */
    public function reduceTimer(User $user, string $timerName, int $secondsToReduce): ?UserTimer
    {
        $timer = $this->getTimer($user, $timerName);
        
        if (!$timer) {
            return null;
        }
        
        $newExpiry = $timer->expires_at->subSeconds($secondsToReduce);
        
        // If timer would be in the past, delete it
        if ($newExpiry->isPast()) {
            $timer->delete();
            return null;
        }
        
        $timer->expires_at = $newExpiry;
        $timer->duration = max(0, $timer->duration - $secondsToReduce);
        $timer->save();
        
        return $timer;
    }
}
