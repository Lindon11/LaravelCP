<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalledModule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'type',
        'description',
        'dependencies',
        'config',
        'enabled',
        'installed_at',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'config' => 'array',
        'enabled' => 'boolean',
        'installed_at' => 'datetime',
    ];

    /**
     * Scope for enabled modules.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for modules only.
     */
    public function scopeModules($query)
    {
        return $query->where('type', 'module');
    }

    /**
     * Scope for themes only.
     */
    public function scopeThemes($query)
    {
        return $query->where('type', 'theme');
    }

    /**
     * Check if module is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the module.
     */
    public function enable(): void
    {
        $this->update(['enabled' => true]);
    }

    /**
     * Disable the module.
     */
    public function disable(): void
    {
        $this->update(['enabled' => false]);
    }
}
