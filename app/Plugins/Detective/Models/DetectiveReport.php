<?php

namespace App\Plugins\Detective\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectiveReport extends Model
{
    protected $fillable = [
        'user_id',
        'target_id',
        'status',
        'location_info',
        'hired_at',
        'complete_at',
    ];

    protected $casts = [
        'hired_at' => 'datetime',
        'complete_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
