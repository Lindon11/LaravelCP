<?php

namespace App\Plugins\Casino\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryTicket extends Model
{
    protected $fillable = [
        'lottery_id',
        'user_id',
        'numbers',
        'is_winner',
        'prize_amount',
        'purchased_at',
    ];

    protected $casts = [
        'numbers' => 'array',
        'is_winner' => 'boolean',
        'purchased_at' => 'datetime',
    ];

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
