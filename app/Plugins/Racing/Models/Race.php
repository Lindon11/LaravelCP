<?php

namespace App\Plugins\Racing\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'description',
        'entry_fee',
        'prize_pool',
        'min_participants',
        'max_participants',
        'status',
        'starts_at',
        'finished_at',
        'winner_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RaceParticipant::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isRacing(): bool
    {
        return $this->status === 'racing';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function canJoin(): bool
    {
        return $this->isWaiting() &&
               $this->participants()->count() < $this->max_participants;
    }

    public function canStart(): bool
    {
        return $this->isWaiting() &&
               $this->participants()->count() >= $this->min_participants;
    }
}
