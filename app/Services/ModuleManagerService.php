<?php

namespace App\Services;

use App\Models\InstalledModule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ModuleManagerService
{
    protected $modulesPath;
    protected $themesPath;

    public function __construct()
    {
        $this->modulesPath = base_path('modules');
        $this->themesPath = base_path('themes');
    }

    /**
     * Get all available modules (installed + uninstalled).
     */
    public function getAllModules(): array
    {
        $installed = InstalledModule::modules()->get()->keyBy('slug');
        $available = [];

        if (File::exists($this->modulesPath)) {
            $directories = File::directories($this->modulesPath);
            
            foreach ($directories as $dir) {
                $slug = basename($dir);
                $moduleJson = $this->loadModuleJson($dir);
                
                if ($moduleJson) {
                    $available[] = [
                        'slug' => $slug,
                        'name' => $moduleJson['name'] ?? $slug,
                        'version' => $moduleJson['version'] ?? '1.0.0',
                        'description' => $moduleJson['description'] ?? '',
                        'author' => $moduleJson['author'] ?? '',
                        'dependencies' => $moduleJson['dependencies'] ?? [],
                        'installed' => $installed->has($slug),
                        'enabled' => $installed->has($slug) ? $installed[$slug]->enabled : false,
                        'path' => $dir,
                    ];
                }
            }
        }

        return $available;
    }

    /**
     * Get all available themes.
     */
    public function getAllThemes(): array
    {
        $installed = InstalledModule::themes()->get()->keyBy('slug');
        $available = [];

        if (File::exists($this->themesPath)) {
            $directories = File::directories($this->themesPath);
            
            foreach ($directories as $dir) {
                $slug = basename($dir);
                $themeJson = $this->loadModuleJson($dir);
                
                if ($themeJson) {
                    $available[] = [
                        'slug' => $slug,
                        'name' => $themeJson['name'] ?? $slug,
                        'version' => $themeJson['version'] ?? '1.0.0',
                        'description' => $themeJson['description'] ?? '',
                        'author' => $themeJson['author'] ?? '',
                        'screenshot' => $themeJson['screenshot'] ?? null,
                        'installed' => $installed->has($slug),
                        'enabled' => $installed->has($slug) ? $installed[$slug]->enabled : false,
                        'path' => $dir,
                    ];
                }
            }
        }

        return $available;
    }

    /**
     * Install a module from directory.
     */
    public function installModule(string $slug): array
    {
        $modulePath = $this->modulesPath . '/' . $slug;

        if (!File::exists($modulePath)) {
            return ['success' => false, 'message' => 'Module directory not found.'];
        }

        $moduleJson = $this->loadModuleJson($modulePath);

        if (!$moduleJson) {
            return ['success' => false, 'message' => 'Invalid module.json file.'];
        }

        // Check if already installed
        if (InstalledModule::where('slug', $slug)->exists()) {
            return ['success' => false, 'message' => 'Module already installed.'];
        }

        // Check dependencies
        $dependencyCheck = $this->checkDependencies($moduleJson['dependencies'] ?? []);
        if (!$dependencyCheck['satisfied']) {
            return [
                'success' => false,
                'message' => 'Missing dependencies: ' . implode(', ', $dependencyCheck['missing'])
            ];
        }

        DB::beginTransaction();

        try {
            // Run module migrations if they exist
            $migrationsPath = $modulePath . '/database/migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate', ['--path' => 'modules/' . $slug . '/database/migrations']);
            }

            // Copy assets if they exist
            $assetsPath = $modulePath . '/assets';
            $publicPath = public_path('modules/' . $slug);
            if (File::exists($assetsPath)) {
                File::copyDirectory($assetsPath, $publicPath);
            }

            // Register module in database
            $module = InstalledModule::create([
                'name' => $moduleJson['name'],
                'slug' => $slug,
                'version' => $moduleJson['version'],
                'type' => 'module',
                'description' => $moduleJson['description'] ?? '',
                'dependencies' => $moduleJson['dependencies'] ?? [],
                'config' => $moduleJson['config'] ?? [],
                'enabled' => true,
                'installed_at' => now(),
            ]);

            DB::commit();

            // Run module installer if it exists (after transaction completes)
            $installerPath = $modulePath . '/src/Installer.php';
            if (File::exists($installerPath)) {
                require_once $installerPath;
                $installerClass = $this->getModuleClass($slug, 'Installer');
                if (class_exists($installerClass)) {
                    $installer = new $installerClass();
                    if (method_exists($installer, 'install')) {
                        $installer->install();
                    }
                }
            }

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return [
                'success' => true,
                'message' => "Module '{$moduleJson['name']}' installed successfully.",
                'module' => $module
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Uninstall a module.
     */
    public function uninstallModule(string $slug): array
    {
        $module = InstalledModule::where('slug', $slug)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed.'];
        }

        $modulePath = $this->modulesPath . '/' . $slug;

        DB::beginTransaction();

        try {
            // Run module uninstaller if it exists
            $installerClass = $this->getModuleClass($slug, 'Installer');
            if ($installerClass && class_exists($installerClass)) {
                $installer = new $installerClass();
                if (method_exists($installer, 'uninstall')) {
                    $installer->uninstall();
                }
            }

            // Rollback migrations
            $migrationsPath = $modulePath . '/database/migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate:rollback', ['--path' => 'modules/' . $slug . '/database/migrations']);
            }

            // Remove assets
            $publicPath = public_path('modules/' . $slug);
            if (File::exists($publicPath)) {
                File::deleteDirectory($publicPath);
            }

            // Remove from database
            $module->delete();

            DB::commit();

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return [
                'success' => true,
                'message' => "Module '{$module->name}' uninstalled successfully."
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Uninstallation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enable a module.
     */
    public function enableModule(string $slug): array
    {
        $module = InstalledModule::where('slug', $slug)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed.'];
        }

        if ($module->enabled) {
            return ['success' => false, 'message' => 'Module already enabled.'];
        }

        $module->enable();

        Artisan::call('config:clear');
        Artisan::call('route:clear');

        return [
            'success' => true,
            'message' => "Module '{$module->name}' enabled successfully."
        ];
    }

    /**
     * Disable a module.
     */
    public function disableModule(string $slug): array
    {
        $module = InstalledModule::where('slug', $slug)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed.'];
        }

        if (!$module->enabled) {
            return ['success' => false, 'message' => 'Module already disabled.'];
        }

        $module->disable();

        Artisan::call('config:clear');
        Artisan::call('route:clear');

        return [
            'success' => true,
            'message' => "Module '{$module->name}' disabled successfully."
        ];
    }

    /**
     * Upload and extract module/theme from ZIP.
     */
    public function uploadAndExtract($zipFile, string $type = 'module'): array
    {
        $zip = new ZipArchive();
        $targetPath = $type === 'theme' ? $this->themesPath : $this->modulesPath;

        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        if ($zip->open($zipFile->getRealPath()) !== true) {
            return ['success' => false, 'message' => 'Failed to open ZIP file.'];
        }

        // Get module slug from first directory in ZIP
        $firstEntry = $zip->getNameIndex(0);
        $slug = explode('/', $firstEntry)[0];

        $extractPath = $targetPath . '/' . $slug;

        // Check if module already exists
        if (File::exists($extractPath)) {
            $zip->close();
            return ['success' => false, 'message' => ucfirst($type) . ' directory already exists.'];
        }

        // Extract
        if (!$zip->extractTo($targetPath)) {
            $zip->close();
            return ['success' => false, 'message' => 'Failed to extract ZIP file.'];
        }

        $zip->close();

        // Verify module.json exists
        if (!File::exists($extractPath . '/module.json')) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'message' => 'Invalid ' . $type . ': missing module.json file.'];
        }

        return [
            'success' => true,
            'message' => ucfirst($type) . ' uploaded successfully.',
            'slug' => $slug
        ];
    }

    /**
     * Upload and extract module from ZIP.
     * @deprecated Use uploadAndExtract() instead
     */
    public function uploadModule($zipFile, string $type = 'module'): array
    {
        $zip = new ZipArchive();
        $targetPath = $type === 'theme' ? $this->themesPath : $this->modulesPath;

        if ($zip->open($zipFile) !== true) {
            return ['success' => false, 'message' => 'Failed to open ZIP file.'];
        }

        // Get module slug from first directory in ZIP
        $firstEntry = $zip->getNameIndex(0);
        $slug = explode('/', $firstEntry)[0];

        $extractPath = $targetPath . '/' . $slug;

        // Check if module already exists
        if (File::exists($extractPath)) {
            $zip->close();
            return ['success' => false, 'message' => ucfirst($type) . ' directory already exists.'];
        }

        // Extract
        if (!$zip->extractTo($targetPath)) {
            $zip->close();
            return ['success' => false, 'message' => 'Failed to extract ZIP file.'];
        }

        $zip->close();

        // Verify module.json exists
        if (!File::exists($extractPath . '/module.json')) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'message' => 'Invalid ' . $type . ': missing module.json file.'];
        }

        return [
            'success' => true,
            'message' => ucfirst($type) . ' uploaded successfully.',
            'slug' => $slug
        ];
    }

    /**
     * Install a theme.
     */
    public function installTheme(string $slug): array
    {
        $themePath = $this->themesPath . '/' . $slug;

        if (!File::exists($themePath)) {
            return ['success' => false, 'message' => 'Theme directory not found.'];
        }

        $themeJson = $this->loadModuleJson($themePath);

        if (!$themeJson) {
            return ['success' => false, 'message' => 'Invalid module.json file.'];
        }

        // Check if already installed
        if (InstalledModule::where('slug', $slug)->where('type', 'theme')->exists()) {
            return ['success' => false, 'message' => 'Theme already installed.'];
        }

        try {
            // Copy theme assets to public
            $assetsPath = $themePath . '/assets';
            $publicPath = public_path('themes/' . $slug);
            if (File::exists($assetsPath)) {
                File::copyDirectory($assetsPath, $publicPath);
            }

            // Register theme in database
            $theme = InstalledModule::create([
                'name' => $themeJson['name'],
                'slug' => $slug,
                'version' => $themeJson['version'],
                'type' => 'theme',
                'description' => $themeJson['description'] ?? '',
                'config' => $themeJson['config'] ?? [],
                'enabled' => false, // Themes are not auto-enabled
                'installed_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Theme '{$themeJson['name']}' installed successfully.",
                'theme' => $theme
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activate a theme (disable others).
     */
    public function activateTheme(string $slug): array
    {
        $theme = InstalledModule::where('slug', $slug)->where('type', 'theme')->first();

        if (!$theme) {
            return ['success' => false, 'message' => 'Theme not installed.'];
        }

        // Disable all other themes
        InstalledModule::themes()->where('id', '!=', $theme->id)->update(['enabled' => false]);

        // Enable this theme
        $theme->enable();

        Artisan::call('view:clear');

        return [
            'success' => true,
            'message' => "Theme '{$theme->name}' activated successfully."
        ];
    }

    /**
     * Load module.json file.
     */
    protected function loadModuleJson(string $path): ?array
    {
        $jsonPath = $path . '/module.json';

        if (!File::exists($jsonPath)) {
            return null;
        }

        $json = File::get($jsonPath);
        return json_decode($json, true);
    }

    /**
     * Check module dependencies.
     */
    protected function checkDependencies(array $dependencies): array
    {
        $missing = [];

        foreach ($dependencies as $depSlug => $version) {
            $installed = InstalledModule::where('slug', $depSlug)->where('enabled', true)->first();
            
            if (!$installed) {
                $missing[] = $depSlug;
            }
        }

        return [
            'satisfied' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Get module class namespace.
     */
    protected function getModuleClass(string $slug, string $className): ?string
    {
        $namespace = 'Modules\\' . str_replace('-', '', ucwords($slug, '-')) . '\\' . $className;
        return $namespace;
    }

    /**
     * Get active theme.
     */
    public function getActiveTheme(): ?InstalledModule
    {
        return InstalledModule::themes()->enabled()->first();
    }

    /**
     * Create example module structure.
     */
    public function createModuleStructure(string $slug, string $name): array
    {
        $modulePath = $this->modulesPath . '/' . $slug;

        if (File::exists($modulePath)) {
            return ['success' => false, 'message' => 'Module directory already exists.'];
        }

        // Create directory structure
        File::makeDirectory($modulePath, 0755, true);
        File::makeDirectory($modulePath . '/src', 0755, true);
        File::makeDirectory($modulePath . '/database/migrations', 0755, true);
        File::makeDirectory($modulePath . '/routes', 0755, true);
        File::makeDirectory($modulePath . '/views', 0755, true);
        File::makeDirectory($modulePath . '/assets', 0755, true);

        // Create module.json
        $moduleJson = [
            'name' => $name,
            'slug' => $slug,
            'version' => '1.0.0',
            'description' => 'Description for ' . $name,
            'author' => 'Your Name',
            'dependencies' => [],
            'config' => []
        ];

        File::put($modulePath . '/module.json', json_encode($moduleJson, JSON_PRETTY_PRINT));

        // Create README
        File::put($modulePath . '/README.md', "# {$name}\n\n{$moduleJson['description']}");

        return [
            'success' => true,
            'message' => "Module structure created at: {$modulePath}",
            'path' => $modulePath
        ];
    }
}
