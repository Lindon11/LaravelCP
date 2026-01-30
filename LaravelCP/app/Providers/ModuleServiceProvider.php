<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Discovered modules
     * @var array
     */
    protected array $modules = [];

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->discoverModules();
        $this->registerModuleConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleViews();
        $this->loadModuleMigrations();
        $this->loadModuleTranslations();
        $this->publishModuleAssets();
        $this->registerModulesToRegistry();
        
        // Make modules available globally
        View::share('modules', $this->modules);
        app()->instance('modules', $this->modules);
    }

    /**
     * Register modules to the registry
     */
    protected function registerModulesToRegistry(): void
    {
        $modulesPath = base_path('modules');
        
        if (!is_dir($modulesPath)) {
            return;
        }

        $modules = scandir($modulesPath);
        
        foreach ($modules as $module) {
            if ($module === '.' || $module === '..') {
                continue;
            }
            
            $modulePath = $modulesPath . '/' . $module;
            $configPath = $modulePath . '/module.json';
            
            if (file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
                if ($config) {
                    \App\Services\ModuleRegistry::register($config);
                }
            }
        }
    }

    /**
     * Discover all modules
     */
    protected function discoverModules(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
            return;
        }

        $moduleDirs = File::directories($modulesPath);

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            $moduleJsonPath = $moduleDir . '/module.json';

            if (File::exists($moduleJsonPath)) {
                $moduleData = json_decode(File::get($moduleJsonPath), true);
                
                $this->modules[$moduleName] = array_merge([
                    'id' => $moduleName,
                    'path' => $moduleDir,
                    'namespace' => "App\\Modules\\{$moduleName}",
                    'enabled' => true,
                ], $moduleData);
            }
        }
    }

    /**
     * Register module configuration
     */
    protected function registerModuleConfig(): void
    {
        foreach ($this->modules as $module) {
            $configPath = $module['path'] . '/config.php';
            
            if (File::exists($configPath)) {
                $this->mergeConfigFrom($configPath, 'modules.' . $module['id']);
            }
        }
    }

    /**
     * Load module routes
     */
    protected function loadModuleRoutes(): void
    {
        foreach ($this->modules as $module) {
            if (!($module['enabled'] ?? true)) {
                continue;
            }

            // Web routes
            $webRoutesPath = $module['path'] . '/routes/web.php';
            if (File::exists($webRoutesPath)) {
                Route::middleware('web')
                    ->namespace($module['namespace'] . '\\Controllers')
                    ->group($webRoutesPath);
            }

            // API routes
            $apiRoutesPath = $module['path'] . '/routes/api.php';
            if (File::exists($apiRoutesPath)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($module['namespace'] . '\\Controllers')
                    ->group($apiRoutesPath);
            }

            // Admin routes
            $adminRoutesPath = $module['path'] . '/routes/admin.php';
            if (File::exists($adminRoutesPath)) {
                Route::prefix('admin')
                    ->middleware(['web', 'auth', 'admin'])
                    ->namespace($module['namespace'] . '\\Controllers')
                    ->name('admin.')
                    ->group($adminRoutesPath);
            }
        }
    }

    /**
     * Load module views
     */
    protected function loadModuleViews(): void
    {
        foreach ($this->modules as $module) {
            $viewsPath = $module['path'] . '/views';
            
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $module['id']);
            }
        }
    }

    /**
     * Load module migrations
     */
    protected function loadModuleMigrations(): void
    {
        foreach ($this->modules as $module) {
            $migrationsPath = $module['path'] . '/database/migrations';
            
            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }

    /**
     * Load module translations
     */
    protected function loadModuleTranslations(): void
    {
        foreach ($this->modules as $module) {
            $langPath = $module['path'] . '/lang';
            
            if (File::exists($langPath)) {
                $this->loadTranslationsFrom($langPath, $module['id']);
            }
        }
    }

    /**
     * Publish module assets
     */
    protected function publishModuleAssets(): void
    {
        foreach ($this->modules as $module) {
            $assetsPath = $module['path'] . '/assets';
            
            if (File::exists($assetsPath)) {
                $this->publishes([
                    $assetsPath => public_path('modules/' . $module['id']),
                ], 'module-' . $module['id'] . '-assets');
            }
        }
    }

    /**
     * Get all enabled modules
     */
    public static function getModules(): array
    {
        return app('modules') ?? [];
    }

    /**
     * Get a specific module
     */
    public static function getModule(string $name): ?array
    {
        $modules = self::getModules();
        return $modules[$name] ?? null;
    }

    /**
     * Check if module is enabled
     */
    public static function isEnabled(string $name): bool
    {
        $module = self::getModule($name);
        return $module && ($module['enabled'] ?? true);
    }
}
