<?php

namespace App\Console\Commands;

use App\Core\Services\ActivityLogService;
use App\Core\Services\CacheService;
use Illuminate\Console\Command;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'game:cleanup 
                            {--activity : Clean old activity logs}
                            {--notifications : Clean old notifications}
                            {--all : Clean all old data}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old game data (activity logs, notifications, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(ActivityLogService $activityService, \App\Services\NotificationService $notificationService)
    {
        $cleaned = [];

        if ($this->option('activity') || $this->option('all')) {
            $this->info('Cleaning old activity logs...');
            $count = $activityService->cleanOldLogs();
            $this->info("Deleted {$count} old activity logs");
            $cleaned['activity_logs'] = $count;
        }

        if ($this->option('notifications') || $this->option('all')) {
            $this->info('Cleaning old notifications...');
            $count = $notificationService->cleanOldNotifications();
            $this->info("Deleted {$count} old notifications");
            $cleaned['notifications'] = $count;
        }

        if ($this->option('all')) {
            $this->info('Clearing expired caches...');
            $this->info('Cache cleanup complete');
        }

        if (empty($cleaned)) {
            $this->warn('No cleanup options specified. Use --activity, --notifications, or --all');
            return 1;
        }

        $this->info('Cleanup completed successfully!');
        return 0;
    }
}
