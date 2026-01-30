<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $fillable = [
        'name',
        'description',
        'value',
        'theft_chance',
        'required_level',
    ];

    protected $casts = [
        'value' => 'integer',
        'theft_chance' => 'integer',
        'required_level' => 'integer',
    ];

    public function garageEntries(): HasMany
    {
        return $this->hasMany(Garage::class);
    }

    public function theftAttempts(): HasMany
    {
        return $this->hasMany(TheftAttempt::class);
    }
}
