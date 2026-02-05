<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CasinoGameType extends Model
{
    protected $fillable = ['name', 'label', 'icon', 'sort_order'];
}
