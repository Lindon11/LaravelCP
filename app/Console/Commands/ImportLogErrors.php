<?php

namespace App\Console\Commands;

use App\Core\Models\ErrorLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportLogErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:import-errors {--days=7 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import errors from Laravel log files into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Scanning log files from the last {$days} days...");
        
        $logPath = storage_path('logs');
        $imported = 0;
        $skipped = 0;
        
        // Get log files
        $logFiles = collect(File::files($logPath))
            ->filter(fn($file) => str_ends_with($file->getFilename(), '.log'))
            ->filter(fn($file) => $file->getMTime() > now()->subDays($days)->timestamp)
            ->sortByDesc(fn($file) => $file->getMTime());

        if ($logFiles->isEmpty()) {
            $this->warn('No log files found!');
            return 0;
        }

        $this->info("Found {$logFiles->count()} log file(s) to scan.");

        foreach ($logFiles as $logFile) {
            $this->line("Processing: {$logFile->getFilename()}");
            
            $content = File::get($logFile->getPathname());
            
            // Parse log entries (Laravel log format)
            preg_match_all(
                '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+?)(?=\n\[|\Z)/s',
                $content,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $level = strtoupper($match[3]);
                
                // Only import ERROR and CRITICAL levels
                if (!in_array($level, ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'])) {
                    continue;
                }

                $message = trim($match[4]);
                
                // Extract exception details if present
                if (preg_match('/^([^:]+): (.+?)(?:\n|$)/s', $message, $exceptionMatch)) {
                    $type = $exceptionMatch[1];
                    $errorMessage = trim($exceptionMatch[2]);
                } else {
                    $type = 'Log Error';
                    $errorMessage = $message;
                }

                // Extract file and line if present
                $file = null;
                $line = null;
                if (preg_match('/in (.+?):(\d+)/i', $message, $fileMatch)) {
                    $file = $fileMatch[1];
                    $line = (int) $fileMatch[2];
                }

                // Extract stack trace
                $trace = null;
                if (preg_match('/Stack trace:(.+?)(?=\n\[|$)/s', $message, $traceMatch)) {
                    $trace = trim($traceMatch[1]);
                } elseif (preg_match('/#0 .+/s', $message, $traceMatch)) {
                    $trace = $traceMatch[0];
                }

                // Check if this error already exists
                $existing = ErrorLog::where('type', $type)
                    ->where('message', $errorMessage)
                    ->when($file, fn($q) => $q->where('file', $file))
                    ->when($line, fn($q) => $q->where('line', $line))
                    ->first();

                if ($existing) {
                    $existing->increment('count');
                    $existing->update(['last_seen_at' => $match[1]]);
                    $skipped++;
                } else {
                    ErrorLog::create([
                        'type' => $type,
                        'message' => substr($errorMessage, 0, 65000), // Prevent text overflow
                        'file' => $file,
                        'line' => $line,
                        'trace' => $trace ? substr($trace, 0, 65000) : null,
                        'url' => null,
                        'method' => null,
                        'ip' => null,
                        'user_id' => null,
                        'user_agent' => null,
                        'context' => ['source' => 'log_import'],
                        'last_seen_at' => $match[1],
                        'created_at' => $match[1],
                        'updated_at' => now(),
                    ]);
                    $imported++;
                }
            }
        }

        $this->newLine();
        $this->info("✅ Import complete!");
        $this->line("Imported: {$imported} new errors");
        $this->line("Updated: {$skipped} existing errors");

        return 0;
    }
}
