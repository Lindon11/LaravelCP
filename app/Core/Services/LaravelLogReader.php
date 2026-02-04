<?php

namespace App\Core\Services;

use App\Core\Models\ErrorLog;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LaravelLogReader
{
    /**
     * Path to the Laravel log file
     */
    protected string $logPath;

    /**
     * Pattern to match log entries
     */
    protected string $logPattern = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{4})?)\]\s+(\w+)\.(\w+):\s+(.*)$/m';

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    /**
     * Parse the Laravel log file and return recent entries
     *
     * @param int $limit Maximum number of entries to return
     * @param Carbon|null $since Only return entries after this date
     * @return array
     */
    public function parseLogFile(int $limit = 100, ?Carbon $since = null): array
    {
        if (!File::exists($this->logPath)) {
            return [];
        }

        $content = File::get($this->logPath);
        $entries = [];

        // Split by log entry pattern (each entry starts with [date])
        $rawEntries = preg_split('/(?=\[\d{4}-\d{2}-\d{2})/', $content, -1, PREG_SPLIT_NO_EMPTY);

        // Process in reverse to get most recent first
        $rawEntries = array_reverse($rawEntries);

        foreach ($rawEntries as $rawEntry) {
            if (count($entries) >= $limit) {
                break;
            }

            $entry = $this->parseLogEntry($rawEntry);

            if ($entry === null) {
                continue;
            }

            // Filter by date if specified
            if ($since && $entry['timestamp']->lt($since)) {
                continue;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Parse a single log entry
     */
    protected function parseLogEntry(string $rawEntry): ?array
    {
        // Match the log header
        if (!preg_match($this->logPattern, $rawEntry, $matches)) {
            return null;
        }

        $timestamp = $matches[1];
        $environment = $matches[2];
        $level = strtolower($matches[3]);
        $message = trim($matches[4]);

        // Extract stack trace if present
        $trace = null;
        $messageLines = explode("\n", $rawEntry);
        if (count($messageLines) > 1) {
            // First line is the header, rest might be stack trace
            $trace = implode("\n", array_slice($messageLines, 1));
        }

        // Try to extract file and line from message or trace
        $file = null;
        $line = null;

        // Look for file:line pattern in message
        if (preg_match('/in\s+([^:]+):(\d+)/', $rawEntry, $fileMatch)) {
            $file = $fileMatch[1];
            $line = (int) $fileMatch[2];
        } elseif (preg_match('/([\/\w\-\.]+\.php):(\d+)/', $rawEntry, $fileMatch)) {
            $file = $fileMatch[1];
            $line = (int) $fileMatch[2];
        }

        try {
            $parsedTimestamp = Carbon::parse($timestamp);
        } catch (\Exception $e) {
            $parsedTimestamp = now();
        }

        return [
            'timestamp' => $parsedTimestamp,
            'environment' => $environment,
            'level' => $level,
            'message' => $message,
            'trace' => $trace,
            'file' => $file,
            'line' => $line,
            'raw' => $rawEntry,
        ];
    }

    /**
     * Sync log file entries to the database
     * Only imports entries not already in the database
     *
     * @param int $limit Maximum entries to sync
     * @return int Number of new entries imported
     */
    public function syncToDatabase(int $limit = 50): int
    {
        // Get the most recent laravel-log entry to avoid duplicates
        $lastEntry = ErrorLog::whereJsonContains('context->app_source', 'laravel-log')
            ->orderBy('created_at', 'desc')
            ->first();

        $since = $lastEntry ? $lastEntry->created_at->subMinute() : now()->subDay();

        $entries = $this->parseLogFile($limit, $since);
        $imported = 0;

        foreach ($entries as $entry) {
            // Skip if this exact error already exists
            $exists = ErrorLog::where('type', 'LaravelLog' . ucfirst($entry['level']))
                ->where('message', $entry['message'])
                ->where('file', $entry['file'])
                ->where('line', $entry['line'])
                ->whereRaw("created_at >= ?", [$entry['timestamp']->subMinute()])
                ->whereRaw("created_at <= ?", [$entry['timestamp']->addMinute()])
                ->exists();

            if ($exists) {
                continue;
            }

            // Map log level to severity
            $severity = $this->mapLevelToSeverity($entry['level']);

            ErrorLog::create([
                'type' => 'LaravelLog' . ucfirst($entry['level']),
                'message' => $entry['message'],
                'file' => $entry['file'] ?? 'laravel.log',
                'line' => $entry['line'] ?? 0,
                'trace' => $entry['trace'],
                'url' => null,
                'method' => null,
                'ip' => null,
                'user_id' => null,
                'user_agent' => null,
                'context' => [
                    'environment' => $entry['environment'],
                    'log_level' => $entry['level'],
                    'severity' => $severity,
                    'app_source' => 'laravel-log',
                    'from_log_file' => true,
                ],
                'last_seen_at' => $entry['timestamp'],
                'created_at' => $entry['timestamp'],
            ]);

            $imported++;
        }

        return $imported;
    }

    /**
     * Map Laravel log level to severity
     */
    protected function mapLevelToSeverity(string $level): string
    {
        return match ($level) {
            'emergency' => 'emergency',
            'alert' => 'critical',
            'critical' => 'critical',
            'error' => 'error',
            'warning' => 'warning',
            'notice' => 'info',
            'info' => 'info',
            'debug' => 'debug',
            default => 'error',
        };
    }

    /**
     * Get log file statistics
     */
    public function getStats(): array
    {
        if (!File::exists($this->logPath)) {
            return [
                'exists' => false,
                'size' => 0,
                'modified' => null,
                'entry_count' => 0,
            ];
        }

        $entries = $this->parseLogFile(1000);
        $levelCounts = [];

        foreach ($entries as $entry) {
            $level = $entry['level'];
            $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
        }

        return [
            'exists' => true,
            'size' => File::size($this->logPath),
            'size_formatted' => $this->formatBytes(File::size($this->logPath)),
            'modified' => Carbon::createFromTimestamp(File::lastModified($this->logPath)),
            'entry_count' => count($entries),
            'by_level' => $levelCounts,
        ];
    }

    /**
     * Clear the log file
     */
    public function clearLog(): bool
    {
        if (File::exists($this->logPath)) {
            return File::put($this->logPath, '') !== false;
        }
        return true;
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
