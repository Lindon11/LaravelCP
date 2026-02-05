<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEffectType extends Model
{
    protected $fillable = [
        'name',
        'label',
        'sort_order',
    ];
}
