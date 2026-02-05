<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementStat extends Model
{
    protected $fillable = ['name', 'label', 'sort_order'];
}
