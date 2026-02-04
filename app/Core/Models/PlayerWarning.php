<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerWarning extends Model
{
    protected $fillable = [
        'user_id',
        'issued_by',
        'severity',
        'reason',
        'acknowledged',
        'acknowledged_at',
    ];

    protected $casts = [
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
