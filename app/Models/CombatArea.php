<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CombatArea extends Model
{
    protected $fillable = [
        'location_id',
        'name',
        'description',
        'difficulty',
        'min_level',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(CombatLocation::class, 'location_id');
    }

    public function enemies(): HasMany
    {
        return $this->hasMany(CombatEnemy::class, 'area_id');
    }
}
