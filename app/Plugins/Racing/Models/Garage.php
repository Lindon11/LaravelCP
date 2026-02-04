<?php

namespace App\Plugins\Racing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Garage extends Model
{
    protected $fillable = [
        'user_id',
        'car_id',
        'damage',
        'location',
    ];

    protected $casts = [
        'damage' => 'integer',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get current value of car (reduced by damage)
     */
    public function getCurrentValue(): int
    {
        $baseValue = $this->car->value;
        $damagePercent = $this->damage;
        return $baseValue - intval($baseValue / 100 * $damagePercent);
    }
}
