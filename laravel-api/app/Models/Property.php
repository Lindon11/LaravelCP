<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'price',
        'income_per_day',
        'required_level',
        'owner_id',
        'purchased_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'income_per_day' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isAvailable(): bool
    {
        return $this->owner_id === null;
    }
}
