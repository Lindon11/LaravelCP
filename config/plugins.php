<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plugins Path
    |--------------------------------------------------------------------------
    |
    | Path to plugins directory
    |
    */
    'path' => app_path('Plugins'),

    /*
    |--------------------------------------------------------------------------
    | Plugins Namespace
    |--------------------------------------------------------------------------
    |
    | Default namespace for plugins
    |
    */
    'namespace' => 'App\\Plugins',

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | Automatically discover and load plugins
    |
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Plugin Structure
    |--------------------------------------------------------------------------
    |
    | Expected directory structure for plugins
    |
    */
    'structure' => [
        'routes' => ['web.php', 'api.php', 'admin.php'],
        'controllers' => 'Controllers',
        'models' => 'Models',
        'views' => 'views',
        'migrations' => 'database/migrations',
        'seeders' => 'database/seeders',
        'hooks' => 'hooks.php',
        'config' => 'config.php',
        'assets' => 'assets',
        'lang' => 'lang',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Middleware
    |--------------------------------------------------------------------------
    |
    | Default middleware for plugin routes
    |
    */
    'middleware' => [
        'web' => ['web', 'auth'],
        'api' => ['api', 'auth:sanctum'],
        'admin' => ['web', 'auth', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Plugins
    |--------------------------------------------------------------------------
    |
    | Plugins that cannot be disabled
    |
    */
    'core_plugins' => [
        'Dashboard',
        'Profile',
        'Settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin Cache
    |--------------------------------------------------------------------------
    |
    | Cache plugin discovery for performance
    |
    */
    'cache' => [
        'enabled' => env('PLUGIN_CACHE_ENABLED', true),
        'key' => 'plugins.cache',
        'ttl' => 3600, // 1 hour
    ],

];
