<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class StockSector extends Model
{
    protected $fillable = ['name', 'label', 'sort_order'];
}
