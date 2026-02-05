<?php

namespace App\Plugins\Tournament\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentRound extends Model
{
    protected $fillable = [
        'tournament_id',
        'round_number',
        'name',
    ];

    protected $casts = [
        'round_number' => 'integer',
    ];

    /**
     * The tournament this round belongs to
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * The matches in this round
     */
    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'round_id');
    }
}
