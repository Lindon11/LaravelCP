<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementType extends Model
{
    protected $fillable = ['name', 'label', 'color', 'icon', 'sort_order'];
}
