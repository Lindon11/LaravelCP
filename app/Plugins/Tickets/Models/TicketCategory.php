<?php

namespace App\Plugins\Tickets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'order',
        'active',
        'is_active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['is_active'];

    public function getIsActiveAttribute()
    {
        return $this->active ?? true;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
