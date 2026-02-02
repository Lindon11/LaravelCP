<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerEmployment extends Model
{
    protected $table = 'player_employment';

    protected $fillable = [
        'user_id',
        'position_id',
        'company_id',
        'salary',
        'performance_rating',
        'hired_at',
        'last_work_at',
        'total_days_worked',
        'total_earned',
        'is_active',
    ];

    protected $casts = [
        'hired_at' => 'datetime',
        'last_work_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(EmploymentPosition::class, 'position_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(WorkShift::class, 'player_employment_id');
    }
}
