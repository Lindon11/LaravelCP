<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'description',
        'travel_cost',
        'required_level',
        'image',
    ];

    protected $casts = [
        'travel_cost' => 'decimal:2',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
