<?php

namespace App\Core\Services;

use App\Core\Models\InstalledPlugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class PluginManagerService
{
    protected $pluginsPath;
    protected $installingPath;
    protected $disabledPath;
    protected $themesPath;

    public function __construct()
    {
        $this->pluginsPath = app_path('Plugins');
        $this->installingPath = storage_path('plugins/installing');
        $this->disabledPath = storage_path('plugins/disabled');
        $this->themesPath = base_path('themes');
    }

    /**
     * Get all available modules (installed + uninstalled).
     */
    public function getAllPlugins(): array
    {
        $installed = InstalledPlugin::plugins()->get()->keyBy('slug');
        $available = [];

        if (File::exists($this->pluginsPath)) {
            $directories = File::directories($this->pluginsPath);

            foreach ($directories as $dir) {
                $slug = strtolower(basename($dir));
                $pluginJson = $this->loadPluginJson($dir);

                if ($pluginJson) {
                    $available[] = [
                        'slug' => $slug,
                        'name' => $pluginJson['name'] ?? $slug,
                        'version' => $pluginJson['version'] ?? '1.0.0',
                        'description' => $pluginJson['description'] ?? '',
                        'author' => $pluginJson['author'] ?? '',
                        'dependencies' => $pluginJson['dependencies'] ?? [],
                        'installed' => $installed->has($slug),
                        'enabled' => $installed->has($slug) ? $installed[$slug]->enabled : false,
                        'status' => 'installed',
                        'path' => $dir,
                    ];
                }
            }
        }

        return $available;
    }

    /**
     * Get modules in staging (installing directory).
     */
    public function getStagingPlugins(): array
    {
        $staging = [];

        if (File::exists($this->installingPath)) {
            $directories = File::directories($this->installingPath);

            foreach ($directories as $dir) {
                $slug = strtolower(basename($dir));
                $pluginJson = $this->loadPluginJson($dir);

                if ($pluginJson) {
                    // Check if this is an upgrade
                    $installedModule = InstalledPlugin::where('slug', $slug)->first();

                    $staging[] = [
                        'slug' => $slug,
                        'name' => $pluginJson['name'] ?? $slug,
                        'version' => $pluginJson['version'] ?? '1.0.0',
                        'description' => $pluginJson['description'] ?? '',
                        'author' => $pluginJson['author'] ?? '',
                        'dependencies' => $pluginJson['dependencies'] ?? [],
                        'installed' => false,
                        'enabled' => false,
                        'status' => 'staging',
                        'is_upgrade' => $installedModule ? true : false,
                        'current_version' => $installedModule ? $installedModule->version : null,
                        'path' => $dir,
                    ];
                }
            }
        }

        return $staging;
    }

    /**
     * Get disabled modules.
     */
    public function getDisabledPlugins(): array
    {
        $disabled = [];

        if (File::exists($this->disabledPath)) {
            $directories = File::directories($this->disabledPath);

            foreach ($directories as $dir) {
                $dirName = basename($dir);
                $slug = strtolower($dirName);
                $pluginJson = $this->loadPluginJson($dir);

                if ($pluginJson) {
                    $disabled[] = [
                        'slug' => $slug,
                        'dir_name' => $dirName,
                        'name' => $pluginJson['name'] ?? $slug,
                        'version' => $pluginJson['version'] ?? '1.0.0',
                        'description' => $pluginJson['description'] ?? '',
                        'author' => $pluginJson['author'] ?? '',
                        'dependencies' => $pluginJson['dependencies'] ?? [],
                        'installed' => false,
                        'enabled' => false,
                        'status' => 'disabled',
                        'path' => $dir,
                    ];
                }
            }
        }

        return $disabled;
    }

    /**
     * Get all available themes.
     */
    public function getAllThemes(): array
    {
        $installed = InstalledPlugin::themes()->get()->keyBy('slug');
        $available = [];

        if (File::exists($this->themesPath)) {
            $directories = File::directories($this->themesPath);

            foreach ($directories as $dir) {
                $slug = basename($dir);
                $themeJson = $this->loadPluginJson($dir);

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
     * Install a module from staging directory.
     */
    public function installPlugin(string $slug): array
    {
        // Check staging directory first
        $stagingPath = $this->installingPath . '/' . $slug;
        $installedPath = $this->pluginsPath . '/' . $slug;

        if (!File::exists($stagingPath)) {
            return ['success' => false, 'message' => 'Module not found in staging.'];
        }

        $modulePath = $stagingPath;

        $pluginJson = $this->loadPluginJson($modulePath);

        if (!$pluginJson) {
            return ['success' => false, 'message' => 'Invalid module.json file.'];
        }

        // Check if this is an upgrade
        $existingModule = InstalledPlugin::where('slug', strtolower($slug))->first();
        $isUpgrade = $existingModule ? true : false;

        // Check dependencies
        $dependencyCheck = $this->checkDependencies($pluginJson['dependencies'] ?? []);
        if (!$dependencyCheck['satisfied']) {
            return [
                'success' => false,
                'message' => 'Missing dependencies: ' . implode(', ', $dependencyCheck['missing'])
            ];
        }

        DB::beginTransaction();

        try {
            // If upgrade, backup old module
            if ($isUpgrade && File::exists($installedPath)) {
                $backupPath = storage_path('plugins/backups/' . $slug . '_' . date('Y-m-d_His'));
                File::makeDirectory(dirname($backupPath), 0755, true);
                File::copyDirectory($installedPath, $backupPath);
                File::deleteDirectory($installedPath);
            }

            // Move from staging to installed
            File::move($stagingPath, $installedPath);

            // Run module migrations if they exist
            $migrationsPath = $installedPath . '/database/migrations';
            if (File::exists($migrationsPath)) {
                Artisan::call('migrate', ['--path' => 'app/Plugins/' . $slug . '/database/migrations']);
            }

            // Copy assets if they exist
            $assetsPath = $installedPath . '/assets';
            $publicPath = public_path('plugins/' . $slug);
            if (File::exists($assetsPath)) {
                File::copyDirectory($assetsPath, $publicPath);
            }

            // Register or update module in database
            if ($isUpgrade) {
                $existingModule->update([
                    'name' => $pluginJson['name'],
                    'version' => $pluginJson['version'],
                    'description' => $pluginJson['description'] ?? '',
                    'dependencies' => $pluginJson['dependencies'] ?? [],
                    'config' => $pluginJson['config'] ?? [],
                ]);
                $module = $existingModule;
            } else {
                $module = InstalledPlugin::create([
                    'name' => $pluginJson['name'],
                    'slug' => strtolower($slug),
                    'version' => $pluginJson['version'],
                    'type' => 'module',
                    'description' => $pluginJson['description'] ?? '',
                    'dependencies' => $pluginJson['dependencies'] ?? [],
                    'config' => $pluginJson['config'] ?? [],
                    'enabled' => true,
                    'installed_at' => now(),
                ]);
            }

            DB::commit();

            // Run module installer if it exists (after transaction completes)
            $installerPath = $installedPath . '/src/Installer.php';
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

            $message = $isUpgrade
                ? "Module '{$pluginJson['name']}' upgraded to version {$pluginJson['version']} successfully."
                : "Module '{$pluginJson['name']}' installed successfully.";

            return [
                'success' => true,
                'message' => $message,
                'module' => $module,
                'is_upgrade' => $isUpgrade
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
    public function uninstallPlugin(string $slug): array
    {
        $module = InstalledPlugin::where('slug', $slug)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed.'];
        }

        $modulePath = $this->pluginsPath . '/' . $slug;

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
                Artisan::call('migrate:rollback', ['--path' => 'plugins/' . $slug . '/database/migrations']);
            }

            // Remove assets
            $publicPath = public_path('plugins/' . $slug);
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
    public function enablePlugin(string $slug): array
    {
        $module = InstalledPlugin::where('slug', $slug)->first();

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
     * Disable a module (move to disabled directory).
     */
    public function disablePlugin(string $slug): array
    {
        $module = InstalledPlugin::where('slug', $slug)->first();

        if (!$module) {
            return ['success' => false, 'message' => 'Module not installed.'];
        }

        if (!$module->enabled) {
            return ['success' => false, 'message' => 'Module already disabled.'];
        }

        // Find actual directory name (case-insensitive)
        $actualDir = $this->findModuleDirectory($this->pluginsPath, $slug);
        if (!$actualDir) {
            return ['success' => false, 'message' => 'Module directory not found.'];
        }

        $installedPath = $this->pluginsPath . '/' . $actualDir;
        $disabledPath = $this->disabledPath . '/' . $actualDir;

        try {
            // Move to disabled directory
            if (!File::exists($this->disabledPath)) {
                File::makeDirectory($this->disabledPath, 0755, true);
            }

            if (File::exists($disabledPath)) {
                File::deleteDirectory($disabledPath);
            }

            File::move($installedPath, $disabledPath);

            $module->disable();

            Artisan::call('config:clear');
            Artisan::call('route:clear');

            return [
                'success' => true,
                'message' => "Module '{$module->name}' disabled successfully."
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to disable: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enable a module (move from disabled directory).
     */
    public function reactivatePlugin(string $slug): array
    {
        // Find actual directory name in disabled folder (case-insensitive)
        $actualDir = $this->findModuleDirectory($this->disabledPath, $slug);
        if (!$actualDir) {
            return ['success' => false, 'message' => 'Module not found in disabled directory.'];
        }

        $disabledPath = $this->disabledPath . '/' . $actualDir;
        $installedPath = $this->pluginsPath . '/' . $actualDir;

        try {
            // Move back to installed directory
            if (File::exists($installedPath)) {
                File::deleteDirectory($installedPath);
            }

            File::move($disabledPath, $installedPath);

            // Enable in database
            $module = InstalledPlugin::where('slug', strtolower($slug))->first();
            if ($module) {
                $module->enable();
            }

            Artisan::call('config:clear');
            Artisan::call('route:clear');

            return [
                'success' => true,
                'message' => 'Module reactivated successfully.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reactivate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove a module from staging.
     */
    public function removeStagingPlugin(string $slug): array
    {
        $stagingPath = $this->installingPath . '/' . $slug;

        if (!File::exists($stagingPath)) {
            return ['success' => false, 'message' => 'Module not found in staging.'];
        }

        try {
            File::deleteDirectory($stagingPath);

            return [
                'success' => true,
                'message' => 'Module removed from staging.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload and extract module/theme from ZIP to staging.
     */
    public function uploadAndExtract($zipFile, string $type = 'module'): array
    {
        $zip = new ZipArchive();
        // Upload to staging directory, not directly to installed
        $targetPath = $type === 'theme' ? $this->themesPath : $this->installingPath;

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

        // Check if module already exists in staging
        if (File::exists($extractPath)) {
            $zip->close();
            return ['success' => false, 'message' => ucfirst($type) . ' already in staging. Remove it first.'];
        }

        // Extract
        if (!$zip->extractTo($targetPath)) {
            $zip->close();
            return ['success' => false, 'message' => 'Failed to extract ZIP file.'];
        }

        $zip->close();

        // Verify module.json exists
        if (!File::exists($extractPath . '/plugin.json')) {
            File::deleteDirectory($extractPath);
            return ['success' => false, 'message' => 'Invalid ' . $type . ': missing module.json file.'];
        }

        // Check if this is an upgrade
        $installedModule = InstalledPlugin::where('slug', strtolower($slug))->first();
        $pluginJson = $this->loadPluginJson($extractPath);
        $isUpgrade = $installedModule ? true : false;

        return [
            'success' => true,
            'message' => ucfirst($type) . ' uploaded to staging successfully.',
            'slug' => $slug,
            'is_upgrade' => $isUpgrade,
            'current_version' => $installedModule ? $installedModule->version : null,
            'new_version' => $pluginJson['version'] ?? '1.0.0',
        ];
    }

    /**
     * Upload and extract module from ZIP.
     * @deprecated Use uploadAndExtract() instead
     */
    public function uploadModule($zipFile, string $type = 'module'): array
    {
        $zip = new ZipArchive();
        $targetPath = $type === 'theme' ? $this->themesPath : $this->pluginsPath;

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
        if (!File::exists($extractPath . '/plugin.json')) {
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

        $themeJson = $this->loadPluginJson($themePath);

        if (!$themeJson) {
            return ['success' => false, 'message' => 'Invalid module.json file.'];
        }

        // Check if already installed
        if (InstalledPlugin::where('slug', $slug)->where('type', 'theme')->exists()) {
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
            $theme = InstalledPlugin::create([
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
        $theme = InstalledPlugin::where('slug', $slug)->where('type', 'theme')->first();

        if (!$theme) {
            return ['success' => false, 'message' => 'Theme not installed.'];
        }

        // Disable all other themes
        InstalledPlugin::themes()->where('id', '!=', $theme->id)->update(['enabled' => false]);

        // Enable this theme
        $theme->enable();

        Artisan::call('view:clear');

        return [
            'success' => true,
            'message' => "Theme '{$theme->name}' activated successfully."
        ];
    }

    /**
     * Find module directory by slug (case-insensitive).
     */
    protected function findModuleDirectory(string $basePath, string $slug): ?string
    {
        if (!File::exists($basePath)) {
            return null;
        }

        $directories = File::directories($basePath);
        $slugLower = strtolower($slug);

        foreach ($directories as $dir) {
            $dirName = basename($dir);
            if (strtolower($dirName) === $slugLower) {
                return $dirName;
            }
        }

        return null;
    }

    /**
     * Load plugin.json file.
     */
    protected function loadPluginJson(string $path): ?array
    {
        $pluginJsonPath = $path . '/plugin.json';

        if (!File::exists($pluginJsonPath)) {
            return null;
        }

        $json = File::get($pluginJsonPath);
        return json_decode($json, true);
    }

    /**
     * Check plugin dependencies.
     */
    protected function checkDependencies(array $dependencies): array
    {
        $missing = [];

        foreach ($dependencies as $depSlug => $version) {
            $installed = InstalledPlugin::where('slug', $depSlug)->where('enabled', true)->first();

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
        $namespace = 'Plugins\\' . str_replace('-', '', ucwords($slug, '-')) . '\\' . $className;
        return $namespace;
    }

    /**
     * Get active theme.
     */
    public function getActiveTheme(): ?InstalledPlugin
    {
        return InstalledPlugin::themes()->enabled()->first();
    }

    /**
     * Create example module structure.
     */
    public function createPluginStructure(string $slug, string $name): array
    {
        $modulePath = $this->pluginsPath . '/' . $slug;

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
        $pluginJson = [
            'name' => $name,
            'slug' => $slug,
            'version' => '1.0.0',
            'description' => 'Description for ' . $name,
            'author' => 'Your Name',
            'dependencies' => [],
            'config' => []
        ];

        File::put($modulePath . '/plugin.json', json_encode($pluginJson, JSON_PRETTY_PRINT));

        // Create README
        File::put($modulePath . '/README.md', "# {$name}\n\n{$pluginJson['description']}");

        return [
            'success' => true,
            'message' => "Module structure created at: {$modulePath}",
            'path' => $modulePath
        ];
    }
}
