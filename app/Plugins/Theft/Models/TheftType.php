<?php

namespace App\Plugins\Theft\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TheftType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'success_rate',
        'jail_multiplier',
        'min_car_value',
        'max_car_value',
        'max_damage',
        'cooldown',
        'required_level',
    ];

    protected $casts = [
        'success_rate' => 'integer',
        'jail_multiplier' => 'integer',
        'min_car_value' => 'integer',
        'max_car_value' => 'integer',
        'max_damage' => 'integer',
        'cooldown' => 'integer',
        'required_level' => 'integer',
    ];

    public function theftAttempts(): HasMany
    {
        return $this->hasMany(TheftAttempt::class);
    }
}
