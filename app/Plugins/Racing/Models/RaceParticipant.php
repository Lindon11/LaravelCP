<?php

namespace App\Plugins\Racing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceParticipant extends Model
{
    protected $fillable = [
        'race_id',
        'user_id',
        'vehicle_id',
        'bet_amount',
        'position',
        'finish_time',
        'winnings',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(PlayerInventory::class, 'vehicle_id');
    }
}
