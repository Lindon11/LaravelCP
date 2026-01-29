<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    |
    | Path to modules directory
    |
    */
    'path' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Modules Namespace
    |--------------------------------------------------------------------------
    |
    | Default namespace for modules
    |
    */
    'namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | Automatically discover and load modules
    |
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Module Structure
    |--------------------------------------------------------------------------
    |
    | Expected directory structure for modules
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
    | Module Middleware
    |--------------------------------------------------------------------------
    |
    | Default middleware for module routes
    |
    */
    'middleware' => [
        'web' => ['web', 'auth'],
        'api' => ['api', 'auth:sanctum'],
        'admin' => ['web', 'auth', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Modules
    |--------------------------------------------------------------------------
    |
    | Modules that cannot be disabled
    |
    */
    'core_modules' => [
        'Dashboard',
        'Profile',
        'Settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Cache
    |--------------------------------------------------------------------------
    |
    | Cache module discovery for performance
    |
    */
    'cache' => [
        'enabled' => env('MODULE_CACHE_ENABLED', true),
        'key' => 'modules.cache',
        'ttl' => 3600, // 1 hour
    ],

];
