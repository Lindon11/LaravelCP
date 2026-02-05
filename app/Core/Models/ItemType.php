<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $fillable = [
        'name',
        'label',
        'icon',
        'sort_order',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'type', 'name');
    }
}
