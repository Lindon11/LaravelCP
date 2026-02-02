<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'rating',
        'owner_id',
        'employees_count',
        'total_profit',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'integer',
        'employees_count' => 'integer',
        'total_profit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(EmploymentPosition::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(PlayerEmployment::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
