<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Discovered plugins
     * @var array
     */
    protected array $plugins = [];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->discoverPlugins();
        $this->registerPluginConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadPluginRoutes();
        $this->loadPluginViews();
        $this->loadPluginMigrations();
        $this->loadPluginTranslations();
        $this->publishPluginAssets();
        $this->registerPluginsToRegistry();

        // Make plugins available globally
        View::share('plugins', $this->plugins);
        app()->instance('plugins', $this->plugins);

        // Backwards compatibility
        View::share('modules', $this->plugins);
        app()->instance('modules', $this->plugins);
    }

    /**
     * Register plugins to the registry
     */
    protected function registerPluginsToRegistry(): void
    {
        $pluginsPath = app_path('Plugins');

        if (!is_dir($pluginsPath)) {
            return;
        }

        $plugins = scandir($pluginsPath);

        foreach ($plugins as $plugin) {
            if ($plugin === '.' || $plugin === '..' || !is_dir($pluginsPath . '/' . $plugin)) {
                continue;
            }

            $pluginPath = $pluginsPath . '/' . $plugin;
            $configPath = $pluginPath . '/plugin.json';

            // Also check for module.json for backwards compatibility
            if (!file_exists($configPath)) {
                $configPath = $pluginPath . '/module.json';
            }

            if (file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
                if ($config && class_exists(\App\Core\Services\PluginRegistry::class)) {
                    \App\Core\Services\PluginRegistry::register($config);
                }
            }
        }
    }

    /**
     * Discover all plugins
     */
    protected function discoverPlugins(): void
    {
        $pluginsPath = app_path('Plugins');

        if (!File::exists($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
            return;
        }

        $pluginDirs = File::directories($pluginsPath);

        foreach ($pluginDirs as $pluginDir) {
            $pluginName = basename($pluginDir);

            // Skip the base Plugin.php file
            if ($pluginName === 'Plugin.php') {
                continue;
            }

            $pluginJsonPath = $pluginDir . '/plugin.json';

            // Also check for module.json for backwards compatibility
            if (!File::exists($pluginJsonPath)) {
                $pluginJsonPath = $pluginDir . '/module.json';
            }

            if (File::exists($pluginJsonPath)) {
                $pluginData = json_decode(File::get($pluginJsonPath), true);

                $this->plugins[$pluginName] = array_merge([
                    'id' => $pluginName,
                    'path' => $pluginDir,
                    'namespace' => "App\\Plugins\\{$pluginName}",
                    'enabled' => true,
                ], $pluginData ?? []);
            }
        }
    }

    /**
     * Register plugin configuration
     */
    protected function registerPluginConfig(): void
    {
        foreach ($this->plugins as $plugin) {
            $configPath = $plugin['path'] . '/config.php';

            if (File::exists($configPath)) {
                $this->mergeConfigFrom($configPath, 'plugins.' . $plugin['id']);
            }
        }
    }

    /**
     * Load plugin routes
     */
    protected function loadPluginRoutes(): void
    {
        foreach ($this->plugins as $plugin) {
            if (!($plugin['enabled'] ?? true)) {
                continue;
            }

            // Web routes
            $webRoutesPath = $plugin['path'] . '/routes/web.php';
            if (File::exists($webRoutesPath)) {
                Route::middleware('web')
                    ->namespace($plugin['namespace'] . '\\Controllers')
                    ->group($webRoutesPath);
            }

            // API routes
            $apiRoutesPath = $plugin['path'] . '/routes/api.php';
            if (File::exists($apiRoutesPath)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($plugin['namespace'] . '\\Controllers')
                    ->group($apiRoutesPath);
            }

            // Admin routes
            $adminRoutesPath = $plugin['path'] . '/routes/admin.php';
            if (File::exists($adminRoutesPath)) {
                Route::prefix('admin')
                    ->middleware(['web', 'auth', 'admin'])
                    ->namespace($plugin['namespace'] . '\\Controllers')
                    ->name('admin.')
                    ->group($adminRoutesPath);
            }
        }
    }

    /**
     * Load plugin views
     */
    protected function loadPluginViews(): void
    {
        foreach ($this->plugins as $plugin) {
            $viewsPath = $plugin['path'] . '/views';

            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $plugin['id']);
            }
        }
    }

    /**
     * Load plugin migrations
     */
    protected function loadPluginMigrations(): void
    {
        foreach ($this->plugins as $plugin) {
            $migrationsPath = $plugin['path'] . '/database/migrations';

            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }

    /**
     * Load plugin translations
     */
    protected function loadPluginTranslations(): void
    {
        foreach ($this->plugins as $plugin) {
            $langPath = $plugin['path'] . '/lang';

            if (File::exists($langPath)) {
                $this->loadTranslationsFrom($langPath, $plugin['id']);
            }
        }
    }

    /**
     * Publish plugin assets
     */
    protected function publishPluginAssets(): void
    {
        foreach ($this->plugins as $plugin) {
            $assetsPath = $plugin['path'] . '/assets';

            if (File::exists($assetsPath)) {
                $this->publishes([
                    $assetsPath => public_path('plugins/' . $plugin['id']),
                ], 'plugin-' . $plugin['id'] . '-assets');
            }
        }
    }

    /**
     * Get all enabled plugins
     */
    public static function getPlugins(): array
    {
        return app('plugins') ?? [];
    }

    /**
     * Get a specific plugin
     */
    public static function getPlugin(string $name): ?array
    {
        $plugins = self::getPlugins();
        return $plugins[$name] ?? null;
    }

    /**
     * Check if plugin is enabled
     */
    public static function isEnabled(string $name): bool
    {
        $plugin = self::getPlugin($name);
        return $plugin && ($plugin['enabled'] ?? true);
    }

    // Backwards compatibility static methods
    public static function getModules(): array
    {
        return self::getPlugins();
    }

    public static function getModule(string $name): ?array
    {
        return self::getPlugin($name);
    }
}
