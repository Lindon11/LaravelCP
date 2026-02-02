<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lottery extends Model
{
    protected $fillable = [
        'name',
        'description',
        'ticket_price',
        'prize_pool',
        'winner_user_id',
        'winning_numbers',
        'status',
        'draw_date',
    ];

    protected $casts = [
        'winning_numbers' => 'array',
        'draw_date' => 'datetime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }
}
