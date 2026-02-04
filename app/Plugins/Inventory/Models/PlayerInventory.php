<?php

namespace App\Plugins\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerInventory extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'equipped',
        'slot',
    ];

    protected $casts = [
        'equipped' => 'boolean',
    ];

    public function player()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
