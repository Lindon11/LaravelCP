<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRarity extends Model
{
    protected $fillable = ['name', 'label', 'color', 'sort_order'];
}
