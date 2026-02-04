<?php

namespace App\Console\Commands;

use App\Core\Services\LaravelLogReader;
use Illuminate\Console\Command;

class SyncLaravelLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:sync
                            {--limit=50 : Maximum number of log entries to sync}
                            {--stats : Show log file statistics}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Laravel log file entries to the error_logs database table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logReader = new LaravelLogReader();

        if ($this->option('stats')) {
            $stats = $logReader->getStats();

            $this->info('Laravel Log File Statistics:');
            $this->line('');

            if (!$stats['exists']) {
                $this->warn('Log file does not exist.');
                return 0;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['File Size', $stats['size_formatted']],
                    ['Last Modified', $stats['modified']->format('Y-m-d H:i:s')],
                    ['Total Entries', $stats['entry_count']],
                ]
            );

            if (!empty($stats['by_level'])) {
                $this->line('');
                $this->info('Entries by Level:');
                $levelData = [];
                foreach ($stats['by_level'] as $level => $count) {
                    $levelData[] = [ucfirst($level), $count];
                }
                $this->table(['Level', 'Count'], $levelData);
            }

            return 0;
        }

        $limit = (int) $this->option('limit');

        $this->info("Syncing up to {$limit} log entries...");

        $imported = $logReader->syncToDatabase($limit);

        if ($imported > 0) {
            $this->info("Successfully imported {$imported} new log entries.");
        } else {
            $this->info('No new log entries to import.');
        }

        return 0;
    }
}
