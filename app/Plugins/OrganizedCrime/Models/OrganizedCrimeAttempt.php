<?php

namespace App\Plugins\OrganizedCrime\Models;

use App\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrganizedCrimeAttempt extends Model
{
    protected $fillable = [
        'organized_crime_id',
        'gang_id',
        'leader_id',
        'success',
        'reward_earned',
        'participants',
        'result_message',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'participants' => 'array',
        'attempted_at' => 'datetime',
    ];

    public function organizedCrime()
    {
        return $this->belongsTo(OrganizedCrime::class);
    }

    public function gang()
    {
        return $this->belongsTo(Gang::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }
}
