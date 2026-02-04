<?php

namespace App\Plugins\Bounty\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bounty extends Model
{
    protected $fillable = [
        'target_id',
        'placed_by',
        'claimed_by',
        'amount',
        'status',
        'reason',
        'claimed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'claimed_at' => 'datetime',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function placer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
