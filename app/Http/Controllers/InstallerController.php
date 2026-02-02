<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallerController extends Controller
{
    /**
     * Check if installation is already complete
     */
    protected function isInstalled()
    {
        // Allow preview mode
        if (request()->has('preview')) {
            return false;
        }
        return File::exists(storage_path('installed'));
    }

    /**
     * Welcome page
     */
    public function index()
    {
        if ($this->isInstalled()) {
            return response()->json(['installed' => true, 'status' => 'already_installed']);
        }

        return response()->json(['installed' => false, 'status' => 'ready']);
    }

    /**
     * Check system requirements
     */
    public function requirements()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $requirements = [
            'php' => [
                'version' => PHP_VERSION,
                'required' => '8.2.0',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>=')
            ],
            'extensions' => [
                'PDO' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'tokenizer' => extension_loaded('tokenizer'),
                'json' => extension_loaded('json'),
                'curl' => extension_loaded('curl'),
                'zip' => extension_loaded('zip'),
                'gd' => extension_loaded('gd'),
            ]
        ];

        $permissions = [
            'storage/app' => is_writable(storage_path('app')),
            'storage/framework' => is_writable(storage_path('framework')),
            'storage/logs' => is_writable(storage_path('logs')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
        ];

        $allRequirementsMet = $requirements['php']['status'] && 
                              !in_array(false, $requirements['extensions']) &&
                              !in_array(false, $permissions);

        return response()->json(compact('requirements', 'permissions', 'allRequirementsMet'));
    }

    /**
     * Show database configuration form
     */
    public function database()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return response()->json(['status' => 'ready']);
    }

    /**
     * Test database connection and save configuration
     */
    public function databaseStore(Request $request)
    {
        $data = $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        // Test connection
        try {
            $pdo = new \PDO(
                "mysql:host={$data['db_host']};port={$data['db_port']};dbname={$data['db_name']}",
                $data['db_username'],
                $data['db_password'] ?? ''
            );
            $pdo = null;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Database connection failed: ' . $e->getMessage()
            ], 422);
        }

        // Update .env file
        $this->updateEnvFile([
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_name'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        return response()->json(['success' => true, 'message' => 'Database configuration saved']);
    }

    /**
     * Show application settings form
     */
    public function settings()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return response()->json(['status' => 'ready']);
    }

    /**
     * Save application settings
     */
    public function settingsStore(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_env' => 'required|in:production,local',
        ]);

        $this->updateEnvFile([
            'APP_NAME' => $request->app_name,
            'APP_URL' => $request->app_url,
            'APP_ENV' => $request->app_env,
            'APP_DEBUG' => $request->app_env === 'local' ? 'true' : 'false',
        ]);

        // Generate app key if not exists
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
    }

    /**
     * Run installation
     */
    public function install()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return response()->json(['status' => 'ready']);
    }

    /**
     * Execute installation steps (legacy - all at once)
     */
    public function installProcess(Request $request)
    {
        try {
            // Clear config cache
            Artisan::call('config:clear');
            
            // Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Create storage link
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            // Seed database with game data
            Artisan::call('db:seed', ['--force' => true]);

            return response()->json(['success' => true, 'message' => 'Installation completed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 1: Clear configuration cache
     */
    public function stepClearCache()
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            return response()->json([
                'success' => true, 
                'message' => 'Configuration cache cleared',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 2: Run database migrations
     */
    public function stepMigrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            
            // Count migrations run
            preg_match_all('/Migrating/', $output, $matches);
            $count = count($matches[0]);
            
            return response()->json([
                'success' => true, 
                'message' => $count > 0 ? "Ran {$count} migrations" : "Database is up to date",
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 3: Seed the database
     */
    public function stepSeed()
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);
            return response()->json([
                'success' => true, 
                'message' => 'Game data seeded successfully',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 4: Create storage symbolic link
     */
    public function stepStorageLink()
    {
        try {
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $message = 'Storage link created';
            } else {
                $message = 'Storage link already exists';
            }
            return response()->json([
                'success' => true, 
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 5: Finalize installation
     */
    public function stepFinalize()
    {
        try {
            // Optimize for production
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            
            // Remove installer directory for security
            $installerPath = public_path('install');
            if (is_dir($installerPath)) {
                $this->deleteDirectory($installerPath);
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'Application optimized for production and installer removed'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Recursively delete a directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    /**
     * Show admin account creation form
     */
    public function admin()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return response()->json(['status' => 'ready']);
    }

    /**
     * Create admin account
     */
    public function adminStore(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->username,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => now(),
            'rank_id' => 1, // Thug rank
            'rank' => 'Thug',
            'location' => 'Detroit',
            'location_id' => 1, // Start in Detroit
        ]);

        // Assign admin role (highest level access)
        $user->assignRole('admin');

        return response()->json(['success' => true, 'message' => 'Admin account created successfully']);
    }

    /**
     * Installation complete
     */
    public function complete()
    {
        // Mark as installed
        File::put(storage_path('installed'), now()->toDateTimeString());

        // Clear all caches
        Artisan::call('optimize:clear');

        return response()->json(['success' => true, 'message' => 'Installation complete']);
    }

    /**
     * Update .env file with new values
     */
    protected function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            File::copy(base_path('.env.example'), $envFile);
        }

        $envContent = File::get($envFile);

        foreach ($data as $key => $value) {
            // Escape special characters in value
            $value = str_replace('"', '\"', $value);
            
            // Check if key exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$value}\"",
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envFile, $envContent);
    }
}
