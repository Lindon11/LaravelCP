<?php

namespace App\Console\Commands;

use App\Core\Models\InstalledPlugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RegisterExistingPlugins extends Command
{
    protected $signature = 'plugins:register';
    protected $description = 'Register existing plugins in the database';

    public function handle()
    {
        $pluginsPath = app_path('Plugins');

        if (!File::exists($pluginsPath)) {
            $this->error('Plugins directory not found!');
            return 1;
        }

        $directories = File::directories($pluginsPath);
        $registered = 0;
        $skipped = 0;

        foreach ($directories as $dir) {
            $slug = strtolower(basename($dir));
            $pluginJsonPath = $dir . '/plugin.json';

            if (!File::exists($pluginJsonPath)) {
                $this->warn("Skipping {$slug} - no plugin.json found");
                $skipped++;
                continue;
            }

            $pluginJson = json_decode(File::get($pluginJsonPath), true);

            if (!$pluginJson) {
                $this->warn("Skipping {$slug} - invalid plugin.json");
                $skipped++;
                continue;
            }

            // Check if already registered
            if (InstalledPlugin::where('slug', $slug)->exists()) {
                $this->info("Skipping {$slug} - already registered");
                $skipped++;
                continue;
            }

            // Register in database
            InstalledPlugin::create([
                'name' => $pluginJson['name'] ?? ucfirst($slug),
                'slug' => $slug,
                'version' => $pluginJson['version'] ?? '1.0.0',
                'type' => 'plugin',
                'description' => $pluginJson['description'] ?? '',
                'dependencies' => $pluginJson['requires']['plugins'] ?? [],
                'config' => $pluginJson['settings'] ?? [],
                'enabled' => $pluginJson['enabled'] ?? true,
                'installed_at' => now(),
            ]);

            $this->info("✓ Registered: {$pluginJson['name']} ({$slug})");
            $registered++;
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Registered: {$registered} plugins");
        $this->info("- Skipped: {$skipped} plugins");

        return 0;
    }
}
