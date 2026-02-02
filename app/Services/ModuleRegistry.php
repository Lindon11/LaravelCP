<?php

namespace App\Services;

class ModuleRegistry
{
    protected static $modules = [];

    public static function register($moduleConfig)
    {
        static::$modules[$moduleConfig['id']] = $moduleConfig;
    }

    public static function all()
    {
        return collect(static::$modules)->sortBy('order')->values()->all();
    }

    public static function enabled()
    {
        return collect(static::$modules)->where('enabled', true)->sortBy('order')->values()->all();
    }

    public static function find($id)
    {
        return static::$modules[$id] ?? null;
    }

    public static function clear()
    {
        static::$modules = [];
    }
}
