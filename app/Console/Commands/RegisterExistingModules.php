<?php

namespace App\Console\Commands;

use App\Models\InstalledModule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterExistingModules extends Command
{
    protected $signature = 'modules:register';
    protected $description = 'Register existing modules in the database';

    public function handle()
    {
        $modulesPath = app_path('Modules');
        
        if (!File::exists($modulesPath)) {
            $this->error('Modules directory not found!');
            return 1;
        }

        $directories = File::directories($modulesPath);
        $registered = 0;
        $skipped = 0;

        foreach ($directories as $dir) {
            $slug = strtolower(basename($dir));
            $moduleJsonPath = $dir . '/module.json';

            if (!File::exists($moduleJsonPath)) {
                $this->warn("Skipping {$slug} - no module.json found");
                $skipped++;
                continue;
            }

            $moduleJson = json_decode(File::get($moduleJsonPath), true);

            if (!$moduleJson) {
                $this->warn("Skipping {$slug} - invalid module.json");
                $skipped++;
                continue;
            }

            // Check if already registered
            if (InstalledModule::where('slug', $slug)->exists()) {
                $this->info("Skipping {$slug} - already registered");
                $skipped++;
                continue;
            }

            // Register in database
            InstalledModule::create([
                'name' => $moduleJson['name'] ?? ucfirst($slug),
                'slug' => $slug,
                'version' => $moduleJson['version'] ?? '1.0.0',
                'type' => 'module',
                'description' => $moduleJson['description'] ?? '',
                'dependencies' => $moduleJson['requires']['modules'] ?? [],
                'config' => $moduleJson['settings'] ?? [],
                'enabled' => $moduleJson['enabled'] ?? true,
                'installed_at' => now(),
            ]);

            $this->info("✓ Registered: {$moduleJson['name']} ({$slug})");
            $registered++;
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Registered: {$registered} modules");
        $this->info("- Skipped: {$skipped} modules");

        return 0;
    }
}
