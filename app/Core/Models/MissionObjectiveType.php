<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class MissionObjectiveType extends Model
{
    protected $fillable = ['name', 'label', 'sort_order'];
}
