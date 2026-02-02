<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkShift extends Model
{
    protected $fillable = [
        'user_id',
        'player_employment_id',
        'earnings',
        'performance_score',
        'worked_at',
    ];

    protected $casts = [
        'worked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employment(): BelongsTo
    {
        return $this->belongsTo(PlayerEmployment::class, 'player_employment_id');
    }
}
