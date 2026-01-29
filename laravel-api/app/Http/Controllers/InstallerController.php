<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InstallerController extends Controller
{
    /**
     * Check if installation is already complete
     */
    protected function isInstalled()
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Welcome page
     */
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/')->with('error', 'Application is already installed');
        }

        return Inertia::render('Installer/Welcome');
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

        return Inertia::render('Installer/Requirements', compact('requirements', 'permissions', 'allRequirementsMet'));
    }

    /**
     * Show database configuration form
     */
    public function database()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return Inertia::render('Installer/Database');
    }

    /**
     * Test database connection and save configuration
     */
    public function databaseStore(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        // Test connection
        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}",
                $request->db_username,
                $request->db_password
            );
            $pdo = null;
        } catch (\Exception $e) {
            return back()->withErrors(['db_connection' => 'Database connection failed: ' . $e->getMessage()])->withInput();
        }

        // Update .env file
        $this->updateEnvFile([
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
        ]);

        return redirect()->route('installer.settings');
    }

    /**
     * Show application settings form
     */
    public function settings()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return Inertia::render('Installer/Settings');
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

        return redirect()->route('installer.install');
    }

    /**
     * Run installation
     */
    public function install()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return Inertia::render('Installer/Install');
    }

    /**
     * Execute installation steps
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

            // Seed database (optional)
            // Artisan::call('db:seed', ['--force' => true]);

            return response()->json(['success' => true, 'message' => 'Installation completed successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show admin account creation form
     */
    public function admin()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return Inertia::render('Installer/Admin');
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

        User::create([
            'name' => $request->username,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('installer.complete');
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

        return Inertia::render('Installer/Complete');
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
