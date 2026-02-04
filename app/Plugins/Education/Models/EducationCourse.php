<?php

namespace App\Plugins\Education\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationCourse extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'required_level',
        'required_intelligence',
        'required_endurance',
        'duration_hours',
        'cost',
        'intelligence_reward',
        'endurance_reward',
        'perks',
        'is_active',
    ];

    protected $casts = [
        'perks' => 'array',
        'is_active' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(UserEducation::class, 'course_id');
    }
}
