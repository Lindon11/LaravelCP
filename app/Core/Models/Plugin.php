<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'route_name',
        'enabled',
        'order',
        'settings',
        'required_level',
        'navigation_config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
        'navigation_config' => 'array',
    ];

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getRoute(): ?string
    {
        return $this->route_name ? route($this->route_name) : null;
    }
}
