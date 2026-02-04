<?php

namespace App\Plugins\Chat\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ChatChannel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'created_by',
        'is_active',
        'max_members',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($channel) {
            if (empty($channel->slug)) {
                $channel->slug = Str::slug($channel->name);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'channel_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'channel_members', 'channel_id', 'user_id')
            ->withPivot(['role', 'is_muted', 'last_read_at'])
            ->withTimestamps();
    }

    public function channelMembers(): HasMany
    {
        return $this->hasMany(ChannelMember::class, 'channel_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }
}
