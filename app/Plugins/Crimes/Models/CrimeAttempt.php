<?php

namespace App\Plugins\Crimes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrimeAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'crime_id',
        'success',
        'jailed',
        'cash_earned',
        'respect_earned',
        'result_message',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'jailed' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(User::class);
    }

    public function crime()
    {
        return $this->belongsTo(Crime::class);
    }
}
