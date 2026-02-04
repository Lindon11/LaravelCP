<?php

namespace App\Plugins\Theft\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheftAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'theft_type_id',
        'car_id',
        'success',
        'caught',
        'car_value',
        'damage',
        'result_message',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'caught' => 'boolean',
        'car_value' => 'integer',
        'damage' => 'integer',
        'attempted_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theftType(): BelongsTo
    {
        return $this->belongsTo(TheftType::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
