<?php

namespace App\Plugins\Employment\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmployment extends Model
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
}
