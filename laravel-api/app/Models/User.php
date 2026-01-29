<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

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
        'location',
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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

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