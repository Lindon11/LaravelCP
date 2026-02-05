<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CrimeDifficulty extends Model
{
    protected $table = 'crime_difficulties';
    protected $fillable = ['name', 'label', 'sort_order'];
}
