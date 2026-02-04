<?php

namespace App\Plugins\Casino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasinoBet extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'bet_amount',
        'payout',
        'profit_loss',
        'result',
        'game_data',
        'played_at',
    ];

    protected $casts = [
        'game_data' => 'array',
        'played_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(CasinoGame::class, 'game_id');
    }
}
