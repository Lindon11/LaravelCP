<?php

namespace App\Console\Commands;

use App\Models\ErrorLog;
use Illuminate\Console\Command;

class CleanErrorLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'errors:clean 
                            {--days=30 : Delete errors older than this many days}
                            {--resolved : Only delete resolved errors}';

    /**
     * The console command description.
     */
    protected $description = 'Clean old error logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $resolvedOnly = $this->option('resolved');

        $this->info("Cleaning error logs older than {$days} days...");

        $query = ErrorLog::where('last_seen_at', '<', now()->subDays($days));

        if ($resolvedOnly) {
            $query->where('resolved', true);
            $this->info('Only deleting resolved errors...');
        }

        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No error logs to clean.');
            return 0;
        }

        if ($this->confirm("Delete {$count} error log(s)?", true)) {
            $deleted = $query->delete();
            $this->info("Successfully deleted {$deleted} error log(s).");
        } else {
            $this->info('Operation cancelled.');
        }

        return 0;
    }
}
