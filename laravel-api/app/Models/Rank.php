<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'required_level',
        'attack_bonus',
        'defense_bonus',
    ];

    protected $casts = [
        'required_level' => 'integer',
        'attack_bonus' => 'integer',
        'defense_bonus' => 'integer',
    ];
}
