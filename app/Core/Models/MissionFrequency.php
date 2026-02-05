<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class MissionFrequency extends Model
{
    protected $table = 'mission_frequencies';
    protected $fillable = ['name', 'label', 'sort_order'];
}
