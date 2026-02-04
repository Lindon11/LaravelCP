<?php

namespace App\Plugins\Casino\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCasinoStats extends Model
{
    protected $fillable = [
        'user_id',
        'total_bets',
        'total_wagered',
        'total_won',
        'total_lost',
        'net_profit',
        'biggest_win',
        'biggest_loss',
        'current_streak',
        'best_streak',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
