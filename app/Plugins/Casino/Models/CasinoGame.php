<?php

namespace App\Plugins\Casino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasinoGame extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'min_bet',
        'max_bet',
        'house_edge',
        'return_to_player',
        'rules',
        'is_active',
    ];

    protected $casts = [
        'rules' => 'array',
        'house_edge' => 'decimal:2',
        'return_to_player' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bets(): HasMany
    {
        return $this->hasMany(CasinoBet::class, 'game_id');
    }
}
