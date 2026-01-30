<?php

namespace App\Console\Commands;

use App\Models\ErrorLog;
use Illuminate\Console\Command;

class AutoResolveErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'errors:auto-resolve {--days=7 : Mark errors as resolved if not seen in X days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically resolve errors that haven\'t occurred recently';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $this->info("Auto-resolving errors not seen since: {$cutoffDate->format('Y-m-d H:i:s')}");
        
        // Find unresolved errors that haven't occurred recently
        $oldErrors = ErrorLog::where('resolved', false)
            ->where('last_seen_at', '<', $cutoffDate)
            ->get();
        
        if ($oldErrors->isEmpty()) {
            $this->info('No errors to auto-resolve.');
            return 0;
        }
        
        $this->info("Found {$oldErrors->count()} error(s) to resolve.");
        
        $resolved = 0;
        foreach ($oldErrors as $error) {
            $daysSince = now()->diffInDays($error->last_seen_at);
            
            $this->line("  • [{$error->type}] {$error->message}");
            $this->line("    Last seen: {$daysSince} days ago");
            
            $error->update(['resolved' => true]);
            $resolved++;
        }
        
        $this->newLine();
        $this->info("✅ Auto-resolved {$resolved} error(s)");
        
        return 0;
    }
}
