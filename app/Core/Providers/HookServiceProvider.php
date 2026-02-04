<?php

namespace App\Core\Providers;

use App\Core\Services\HookService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class HookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the HookService as a singleton
        $this->app->singleton('hook', function ($app) {
            return new HookService();
        });

        // Register the facade alias
        $this->app->alias('hook', HookService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Auto-discover and load hook files from modules
        $this->loadModuleHooks();

        // Register core application hooks
        $this->registerCoreHooks();
    }

    /**
     * Load hooks from all plugins
     */
    protected function loadModuleHooks(): void
    {
        // Get plugins from PluginServiceProvider (if available)
        // Check both 'plugins' and 'modules' for backwards compatibility
        if (!$this->app->bound('plugins') && !$this->app->bound('modules')) {
            return;
        }

        $plugins = $this->app->bound('plugins') ? app('plugins') : (app('modules') ?? []);

        foreach ($plugins as $plugin) {
            if (!($plugin['enabled'] ?? true)) {
                continue;
            }

            $hooksFile = $plugin['path'] . '/hooks.php';

            if (File::exists($hooksFile)) {
                require_once $hooksFile;
            }
        }
    }

    /**
     * Register core application hooks
     */
    protected function registerCoreHooks(): void
    {
        $hook = app('hook');

        // Example: Currency formatting hook
        $hook->register('currencyFormat', function ($money) {
            return '$' . number_format($money, 0);
        });

        // Example: Money display with sign
        $hook->register('moneyDisplay', function ($money) {
            $formatted = '$' . number_format(abs($money), 0);
            return $money < 0 ? "-{$formatted}" : $formatted;
        });

        // Example: User level color
        $hook->register('userLevelColor', function ($userLevel) {
            return match ($userLevel) {
                1 => '#22c55e', // Player - green
                2 => '#3b82f6', // Admin - blue
                3 => '#ef4444', // Banned - red
                default => '#6b7280', // gray
            };
        });

        // Example: Alter module data (for membership benefits, etc.)
        $hook->register('alterModuleData', function ($data) {
            // Pass through by default, modules can modify
            return $data;
        }, 0);

        // Example: Before user action
        $hook->register('beforeUserAction', function ($action) {
            // Log or validate action
            return $action;
        });

        // Example: After user action
        $hook->register('afterUserAction', function ($action) {
            // Achievement checks, statistics, etc.
            return $action;
        });

        // Example: Custom navigation menus
        $hook->register('customMenus', function ($user) {
            return [];
        });

        // Example: Module load interceptor
        $hook->register('moduleLoad', function ($moduleName) {
            // Allow modules to redirect or modify page loading
            return $moduleName;
        });

        // Example: Alter template data
        $hook->register('alterTemplateData', function ($data) {
            return $data;
        });
    }
}
