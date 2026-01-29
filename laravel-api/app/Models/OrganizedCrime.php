<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizedCrime extends Model
{
    protected $fillable = [
        'name',
        'description',
        'success_rate',
        'min_reward',
        'max_reward',
        'required_members',
        'required_level',
        'cooldown',
    ];

    protected $casts = [
        'min_reward' => 'decimal:2',
        'max_reward' => 'decimal:2',
    ];
}
