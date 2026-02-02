<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CombatLocation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'energy_cost',
        'min_level',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function areas(): HasMany
    {
        return $this->hasMany(CombatArea::class, 'location_id');
    }
}
