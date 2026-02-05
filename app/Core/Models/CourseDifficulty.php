<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CourseDifficulty extends Model
{
    protected $table = 'course_difficulties';
    protected $fillable = ['name', 'label', 'sort_order'];
}
