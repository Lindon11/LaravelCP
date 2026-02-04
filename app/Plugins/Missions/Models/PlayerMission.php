<?php

namespace App\Plugins\Missions\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerMission extends Model
{
    protected $fillable = [
        'user_id',
        'mission_id',
        'status',
        'progress',
        'started_at',
        'completed_at',
        'available_again_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'available_again_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function canRepeat(): bool
    {
        if (!$this->available_again_at) {
            return false;
        }

        return now()->gte($this->available_again_at);
    }
}
