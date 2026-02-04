<?php

namespace App\Plugins\Crimes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crime extends Model
{
    /** @use HasFactory<\Database\Factories\CrimeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'success_rate',
        'min_cash',
        'max_cash',
        'experience_reward',
        'respect_reward',
        'cooldown_seconds',
        'energy_cost',
        'required_level',
        'difficulty',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'success_rate' => 'integer',
        'min_cash' => 'integer',
        'max_cash' => 'integer',
        'experience_reward' => 'integer',
        'respect_reward' => 'integer',
        'cooldown_seconds' => 'integer',
        'energy_cost' => 'integer',
        'required_level' => 'integer',
    ];
}
