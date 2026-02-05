<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    protected $fillable = ['name', 'label', 'icon', 'sort_order'];
}
