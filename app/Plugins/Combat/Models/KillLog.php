<?php

namespace App\Plugins\Combat\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KillLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'killer_id',
        'victim_id',
        'bullets_used',
        'damage_dealt',
        'successful',
        'killed_at',
    ];

    protected $casts = [
        'bullets_used' => 'integer',
        'damage_dealt' => 'integer',
        'successful' => 'boolean',
        'killed_at' => 'datetime',
    ];

    public function killer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'killer_id');
    }

    public function victim(): BelongsTo
    {
        return $this->belongsTo(User::class, 'victim_id');
    }
}
