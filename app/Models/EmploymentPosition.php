<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentPosition extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'required_level',
        'required_intelligence',
        'required_endurance',
        'base_salary',
        'max_employees',
        'current_employees',
        'perks',
        'is_active',
    ];

    protected $casts = [
        'perks' => 'array',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(PlayerEmployment::class, 'position_id');
    }
}
