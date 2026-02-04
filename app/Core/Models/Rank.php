<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'required_exp',
        'max_health',
        'cash_reward',
        'bullet_reward',
        'user_limit',
    ];

    protected $casts = [
        'required_exp' => 'integer',
        'max_health' => 'integer',
        'cash_reward' => 'integer',
        'bullet_reward' => 'integer',
        'user_limit' => 'integer',
    ];

    /**
     * Get users at this rank
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }

    /**
     * Get the next rank based on experience
     */
    public static function getNextRank($currentExp)
    {
        return static::where('required_exp', '>', $currentExp)
            ->orderBy('required_exp', 'asc')
            ->first();
    }

    /**
     * Get rank by experience amount
     */
    public static function getRankByExperience($exp)
    {
        return static::where('required_exp', '<=', $exp)
            ->orderBy('required_exp', 'desc')
            ->first();
    }
}
