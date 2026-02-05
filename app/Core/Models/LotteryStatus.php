<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryStatus extends Model
{
    protected $table = 'lottery_statuses';

    protected $fillable = [
        'name',
        'label',
        'color',
        'sort_order',
    ];
}
