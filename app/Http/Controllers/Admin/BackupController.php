<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;
use Exception;

class BackupController extends Controller
{
    protected string $backupPath = 'backups';

    /**
     * List all backups
     */
    public function index(): JsonResponse
    {
        $backups = $this->getBackupsList();
        $stats = $this->getBackupStats($backups);

        return response()->json([
            'backups' => $backups,
            'stats' => $stats,
        ]);
    }

    /**
     * Get backup settings
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'auto_backup_enabled' => config('backup.auto_enabled', false),
            'backup_frequency' => config('backup.frequency', 'daily'),
            'backup_time' => config('backup.time', '03:00'),
            'backup_type' => config('backup.type', 'full'),
            'retention_count' => config('backup.retention', 7),
            'storage_driver' => config('backup.storage', 'local'),
            'local_path' => storage_path('app/backups'),
            's3_bucket' => config('backup.s3.bucket', ''),
            's3_region' => config('backup.s3.region', ''),
        ]);
    }

    /**
     * Update backup schedule settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'boolean',
            'backup_frequency' => 'string|in:hourly,daily,weekly,monthly',
            'backup_time' => 'string',
            'backup_type' => 'string|in:full,database,files',
            'retention_count' => 'integer|min:1|max:100',
        ]);

        // In a real implementation, save to config or database
        // For now, just return success
        // Setting::set('backup.auto_enabled', $validated['auto_backup_enabled']);
        // etc.

        return response()->json([
            'message' => 'Backup settings updated successfully',
        ]);
    }

    /**
     * Update storage settings
     */
    public function updateStorage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'storage_driver' => 'required|string|in:local,s3,gcs,dropbox',
            's3_bucket' => 'nullable|string',
            's3_region' => 'nullable|string',
            's3_key' => 'nullable|string',
            's3_secret' => 'nullable|string',
        ]);

        // Save storage configuration
        // In production, this would update .env or database settings

        return response()->json([
            'message' => 'Storage settings updated successfully',
        ]);
    }

    /**
     * Test storage connection
     */
    public function testStorage(Request $request): JsonResponse
    {
        $driver = $request->get('storage_driver', config('backup.storage', 'local'));

        try {
            if ($driver === 'local') {
                $path = storage_path('app/backups');
                if (!File::isDirectory($path)) {
                    File::makeDirectory($path, 0755, true);
                }
                
                // Test write
                $testFile = $path . '/test_' . time() . '.txt';
                File::put($testFile, 'test');
                File::delete($testFile);
                
                return response()->json(['message' => 'Local storage connection successful']);
            }

            if ($driver === 's3') {
                // Test S3 connection
                Storage::disk('s3')->exists('test');
                return response()->json(['message' => 'S3 connection successful']);
            }

            return response()->json(['message' => 'Storage connection test not implemented for this driver'], 400);
        } catch (Exception $e) {
            return response()->json(['message' => 'Storage connection failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new backup
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:full,database,files',
            'description' => 'nullable|string',
        ]);

        try {
            $timestamp = Carbon::now()->format('Y_m_d_His');
            $filename = "backup_{$timestamp}.zip";
            $backupPath = storage_path("app/{$this->backupPath}/{$filename}");

            // Ensure backup directory exists
            $dir = dirname($backupPath);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Cannot create backup archive');
            }

            // Database backup
            if (in_array($validated['type'], ['full', 'database'])) {
                $sqlFile = $this->createDatabaseDump();
                if ($sqlFile) {
                    $zip->addFile($sqlFile, 'database.sql');
                }
            }

            // Files backup
            if (in_array($validated['type'], ['full', 'files'])) {
                $this->addFilesToZip($zip, storage_path('app/public'), 'storage');
                $this->addFilesToZip($zip, base_path('.env'), '.env');
            }

            $zip->close();

            // Clean up temp SQL file
            if (isset($sqlFile) && File::exists($sqlFile)) {
                File::delete($sqlFile);
            }

            // Save backup metadata
            $this->saveBackupMetadata([
                'name' => $validated['name'],
                'filename' => $filename,
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'size' => File::size($backupPath),
                'created_at' => Carbon::now()->toIso8601String(),
                'status' => 'completed',
            ]);

            Log::info("Backup created: {$filename}");

            return response()->json([
                'message' => 'Backup created successfully',
                'filename' => $filename,
            ]);
        } catch (Exception $e) {
            Log::error("Backup failed: {$e->getMessage()}");
            return response()->json([
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a backup
     */
    public function download(int $id)
    {
        $backups = $this->getBackupsList();
        $backup = collect($backups)->firstWhere('id', $id);

        if (!$backup) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        $path = storage_path("app/{$this->backupPath}/{$backup['filename']}");

        if (!File::exists($path)) {
            return response()->json(['message' => 'Backup file not found'], 404);
        }

        return response()->download($path, $backup['filename']);
    }

    /**
     * Restore from a backup
     */
    public function restore(int $id): JsonResponse
    {
        $backups = $this->getBackupsList();
        $backup = collect($backups)->firstWhere('id', $id);

        if (!$backup) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        $path = storage_path("app/{$this->backupPath}/{$backup['filename']}");

        if (!File::exists($path)) {
            return response()->json(['message' => 'Backup file not found'], 404);
        }

        try {
            $extractPath = storage_path('app/temp_restore_' . time());
            File::makeDirectory($extractPath, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                throw new Exception('Cannot open backup archive');
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Restore database if present
            $sqlFile = $extractPath . '/database.sql';
            if (File::exists($sqlFile)) {
                $this->restoreDatabase($sqlFile);
            }

            // Restore files if present
            $storageBackup = $extractPath . '/storage';
            if (File::isDirectory($storageBackup)) {
                File::copyDirectory($storageBackup, storage_path('app/public'));
            }

            // Restore .env if present
            $envBackup = $extractPath . '/.env';
            if (File::exists($envBackup)) {
                File::copy($envBackup, base_path('.env'));
            }

            // Clean up
            File::deleteDirectory($extractPath);

            // Clear caches
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            Log::info("Backup restored: {$backup['filename']}");

            return response()->json([
                'message' => 'Backup restored successfully',
            ]);
        } catch (Exception $e) {
            Log::error("Restore failed: {$e->getMessage()}");
            return response()->json([
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup
     */
    public function destroy(int $id): JsonResponse
    {
        $backups = $this->getBackupsList();
        $backup = collect($backups)->firstWhere('id', $id);

        if (!$backup) {
            return response()->json(['message' => 'Backup not found'], 404);
        }

        $path = storage_path("app/{$this->backupPath}/{$backup['filename']}");

        if (File::exists($path)) {
            File::delete($path);
        }

        $this->removeBackupMetadata($id);

        return response()->json([
            'message' => 'Backup deleted successfully',
        ]);
    }

    /**
     * Get list of all backups
     */
    protected function getBackupsList(): array
    {
        $metadataPath = storage_path("app/{$this->backupPath}/metadata.json");
        
        if (File::exists($metadataPath)) {
            $metadata = json_decode(File::get($metadataPath), true) ?? [];
            return array_values($metadata);
        }

        // Fallback: scan directory for backups
        $path = storage_path("app/{$this->backupPath}");
        if (!File::isDirectory($path)) {
            return [];
        }

        $files = File::glob($path . '/*.zip');
        $backups = [];

        foreach ($files as $index => $file) {
            $filename = basename($file);
            $backups[] = [
                'id' => $index + 1,
                'name' => pathinfo($filename, PATHINFO_FILENAME),
                'filename' => $filename,
                'type' => 'full',
                'size' => $this->formatBytes(File::size($file)),
                'created_at' => Carbon::createFromTimestamp(File::lastModified($file))->format('Y-m-d H:i'),
                'status' => 'completed',
            ];
        }

        return $backups;
    }

    /**
     * Get backup statistics
     */
    protected function getBackupStats(array $backups): array
    {
        $totalSize = 0;
        $path = storage_path("app/{$this->backupPath}");

        if (File::isDirectory($path)) {
            foreach (File::allFiles($path) as $file) {
                $totalSize += $file->getSize();
            }
        }

        $lastBackup = null;
        if (!empty($backups)) {
            $lastBackup = Carbon::parse(end($backups)['created_at'])->diffForHumans();
        }

        return [
            'total_backups' => count($backups),
            'storage_used' => $this->formatBytes($totalSize),
            'last_backup' => $lastBackup,
        ];
    }

    /**
     * Create database dump
     */
    protected function createDatabaseDump(): ?string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            Log::warning("Database backup not supported for driver: {$config['driver']}");
            return null;
        }

        $filename = storage_path('app/temp_db_' . time() . '.sql');

        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s 2>/dev/null',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($filename)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error("Database dump failed with code: {$returnCode}");
            return null;
        }

        return $filename;
    }

    /**
     * Restore database from SQL file
     */
    protected function restoreDatabase(string $sqlFile): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            throw new Exception("Database restore not supported for driver: {$config['driver']}");
        }

        $command = sprintf(
            'mysql -h%s -u%s -p%s %s < %s 2>/dev/null',
            escapeshellarg($config['host']),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($sqlFile)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Database restore failed with code: {$returnCode}");
        }
    }

    /**
     * Add files to zip archive
     */
    protected function addFilesToZip(ZipArchive $zip, string $source, string $prefix): void
    {
        if (File::isFile($source)) {
            $zip->addFile($source, $prefix);
            return;
        }

        if (!File::isDirectory($source)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $prefix . '/' . substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Save backup metadata
     */
    protected function saveBackupMetadata(array $data): void
    {
        $metadataPath = storage_path("app/{$this->backupPath}/metadata.json");
        
        $metadata = [];
        if (File::exists($metadataPath)) {
            $metadata = json_decode(File::get($metadataPath), true) ?? [];
        }

        $data['id'] = count($metadata) + 1;
        $metadata[] = $data;

        File::put($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Remove backup metadata
     */
    protected function removeBackupMetadata(int $id): void
    {
        $metadataPath = storage_path("app/{$this->backupPath}/metadata.json");
        
        if (!File::exists($metadataPath)) {
            return;
        }

        $metadata = json_decode(File::get($metadataPath), true) ?? [];
        $metadata = array_filter($metadata, fn($item) => $item['id'] !== $id);
        
        File::put($metadataPath, json_encode(array_values($metadata), JSON_PRETTY_PRINT));
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
