<?php

namespace App\Core\Services;

class PluginRegistry
{
    protected static $plugins = [];

    public static function register($pluginConfig)
    {
        $key = $pluginConfig['id'] ?? $pluginConfig['name'] ?? null;
        if ($key) {
            static::$plugins[$key] = $pluginConfig;
        }
    }

    public static function all()
    {
        return collect(static::$plugins)->sortBy('order')->values()->all();
    }

    public static function enabled()
    {
        return collect(static::$plugins)->where('enabled', true)->sortBy('order')->values()->all();
    }

    public static function find($id)
    {
        return static::$plugins[$id] ?? null;
    }

    public static function clear()
    {
        static::$plugins = [];
    }
}
