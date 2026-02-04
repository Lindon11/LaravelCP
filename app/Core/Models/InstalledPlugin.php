<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

class InstalledPlugin extends Model
{
    protected $table = 'installed_plugins';

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
     * Scope for enabled plugins.
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope for plugins only (not themes).
     */
    public function scopePlugins($query)
    {
        return $query->where('type', 'plugin');
    }

    /**
     * Scope for themes only.
     */
    public function scopeThemes($query)
    {
        return $query->where('type', 'theme');
    }

    /**
     * Check if plugin is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the plugin.
     */
    public function enable(): void
    {
        $this->update(['enabled' => true]);
    }

    /**
     * Disable the plugin.
     */
    public function disable(): void
    {
        $this->update(['enabled' => false]);
    }
}
