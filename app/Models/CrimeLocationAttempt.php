<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrimeLocationAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crime_location_id',
        'success',
        'jailed',
        'cash_earned',
        'exp_earned',
        'respect_earned',
        'result_message',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'jailed' => 'boolean',
        'cash_earned' => 'integer',
        'exp_earned' => 'integer',
        'respect_earned' => 'integer',
        'attempted_at' => 'datetime',
    ];

    /**
     * Get the user who attempted the crime
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the crime location
     */
    public function crimeLocation()
    {
        return $this->belongsTo(CrimeLocation::class);
    }
}
