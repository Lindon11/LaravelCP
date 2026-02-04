<?php

namespace App\Plugins\Achievements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'requirement',
        'reward_cash',
        'reward_xp',
        'icon',
        'sort_order'
    ];
    
    protected $casts = [
        'requirement' => 'integer',
        'reward_cash' => 'integer',
        'reward_xp' => 'integer',
        'sort_order' => 'integer'
    ];
    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('progress', 'earned_at')
            ->withTimestamps();
    }
}
