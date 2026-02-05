<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class BountyStatus extends Model
{
    protected $table = 'bounty_statuses';

    protected $fillable = [
        'name',
        'label',
        'color',
        'sort_order',
    ];
}
