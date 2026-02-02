<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'level',
        'experience',
        'strength',
        'defense',
        'speed',
        'health',
        'max_health',
        'energy',
        'max_energy',
        'cash',
        'bank',
        'respect',
        'bullets',
        'rank',
        'rank_id',
        'location',
        'location_id',
        'last_crime_at',
        'last_gta_at',
        'last_active',
        'jail_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_crime_at' => 'datetime',
            'last_gta_at' => 'datetime',
            'last_active' => 'datetime',
            'jail_until' => 'datetime',
        ];
    }

    public function dailyReward()
    {
        return $this->hasOne(DailyReward::class);
    }

    public function currentRank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Check and update user's rank based on experience (V2-style)
     * Returns true if rank changed, false otherwise
     */
    public function checkRank(): bool
    {
        $currentRank = $this->currentRank;
        $nextRank = Rank::getNextRank($this->experience);

        // If no next rank available or user hasn't reached it yet
        if (!$nextRank || $nextRank->required_exp > $this->experience) {
            return false;
        }

        // Check if rank has a user limit
        if ($nextRank->user_limit > 0) {
            $usersAtRank = User::where('rank_id', $nextRank->id)->count();
            if ($usersAtRank >= $nextRank->user_limit) {
                return false; // Rank is full
            }
        }

        // Award rewards and update rank
        $this->cash += $nextRank->cash_reward;
        $this->bullets += $nextRank->bullet_reward;
        $this->max_health = $nextRank->max_health;
        $this->rank_id = $nextRank->id;
        $this->rank = $nextRank->name; // Keep string rank for compatibility
        $this->save();

        // Create notification about rank up
        $this->notifications()->create([
            'type' => 'rank_up',
            'title' => 'Rank Up!',
            'message' => "You have ranked up to {$nextRank->name}! You received \${$nextRank->cash_reward} and {$nextRank->bullet_reward} bullets.",
            'read' => false,
        ]);

        // Check recursively for multiple rank ups
        return $this->checkRank() || true;
    }

    /**
     * Get next rank info for UI display
     */
    public function getNextRankAttribute()
    {
        return Rank::getNextRank($this->experience);
    }

    /**
     * Get experience progress to next rank (percentage)
     */
    public function getExpProgressAttribute(): float
    {
        $current = $this->currentRank;
        $next = $this->next_rank;

        if (!$next || !$current) {
            return 100.0; // Max rank
        }

        $expIntoRank = $this->experience - $current->required_exp;
        $expNeededForNext = $next->required_exp - $current->required_exp;

        return min(100, ($expIntoRank / $expNeededForNext) * 100);
    }
    
    public function crime_attempts()
    {
        return $this->hasMany(CrimeAttempt::class);
    }

    public function properties()
    {
        return $this->hasMany(UserProperty::class);
    }
    
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('progress', 'earned_at')
            ->withTimestamps();
    }
    
    public function timers()
    {
        return $this->hasMany(UserTimer::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function inventory()
    {
        return $this->hasMany(UserInventory::class);
    }

    public function equipment()
    {
        return $this->hasMany(UserEquipment::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(PrivateMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(PrivateMessage::class, 'recipient_id');
    }

    // Chat system relationships
    public function chatChannels()
    {
        return $this->belongsToMany(ChatChannel::class, 'channel_members', 'user_id', 'channel_id')
            ->withPivot(['role', 'is_muted', 'last_read_at'])
            ->withTimestamps();
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }

    public function channelMemberships()
    {
        return $this->hasMany(ChannelMember::class, 'user_id');
    }

    public function sentDirectMessages()
    {
        return $this->hasMany(DirectMessage::class, 'from_user_id');
    }

    public function receivedDirectMessages()
    {
        return $this->hasMany(DirectMessage::class, 'to_user_id');
    }

    public function messageReactions()
    {
        return $this->hasMany(MessageReaction::class, 'user_id');
    }

    public function createdChannels()
    {
        return $this->hasMany(ChatChannel::class, 'created_by');
    }

    // Timer helper methods
    public function hasTimer(string $timerName): bool
    {
        return $this->timers()
            ->where('timer_name', $timerName)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function getTimer(string $timerName): ?UserTimer
    {
        return $this->timers()
            ->where('timer_name', $timerName)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function setTimer(string $timerName, int $seconds, array $metadata = []): UserTimer
    {
        return $this->timers()->updateOrCreate(
            ['timer_name' => $timerName],
            [
                'expires_at' => now()->addSeconds($seconds),
                'duration' => $seconds,
                'metadata' => $metadata,
            ]
        );
    }

    public function clearTimer(string $timerName): void
    {
        $this->timers()->where('timer_name', $timerName)->delete();
    }}