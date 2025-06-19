<?php

namespace Packages\Log\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'log:cleanup 
                            {--days= : Number of days to retain logs (default from config)}
                            {--compress : Compress old logs before deletion}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old log files based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDays = $this->option('days') ?: config('logging-package.file_management.retention_days', 14);
        $compress = $this->option('compress') || config('logging-package.file_management.compress', true);
        $dryRun = $this->option('dry-run');

        $this->info("Starting log cleanup...");
        $this->info("Retention period: {$retentionDays} days");
        $this->info("Compression: " . ($compress ? 'enabled' : 'disabled'));
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be deleted");
        }

        $logPath = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("Scanning log directory: {$logPath}");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        $stats = [
            'scanned' => 0,
            'compressed' => 0,
            'deleted' => 0,
            'errors' => 0,
            'size_freed' => 0,
        ];

        $files = File::allFiles($logPath);

        foreach ($files as $file) {
            $stats['scanned']++;
            
            $fileModifiedTime = Carbon::createFromTimestamp($file->getMTime());
            $filePath = $file->getRealPath();
            $fileName = $file->getFilename();

            // Skip if file is within retention period
            if ($fileModifiedTime->isAfter($cutoffDate)) {
                continue;
            }

            // Skip compressed files from compression step
            if ($compress && !str_ends_with($fileName, '.gz')) {
                if ($this->compressFile($filePath, $dryRun)) {
                    $stats['compressed']++;
                    $this->line("Compressed: {$fileName}");
                    continue; // Keep compressed file
                } else {
                    $stats['errors']++;
                    $this->error("Failed to compress: {$fileName}");
                }
            }

            // Delete old files (or compressed files older than retention)
            if ($this->shouldDeleteFile($fileName, $fileModifiedTime, $cutoffDate)) {
                $fileSize = $file->getSize();
                
                if (!$dryRun) {
                    if (File::delete($filePath)) {
                        $stats['deleted']++;
                        $stats['size_freed'] += $fileSize;
                        $this->line("Deleted: {$fileName}");
                    } else {
                        $stats['errors']++;
                        $this->error("Failed to delete: {$fileName}");
                    }
                } else {
                    $stats['deleted']++;
                    $stats['size_freed'] += $fileSize;
                    $this->line("Would delete: {$fileName} ({$this->formatBytes($fileSize)})");
                }
            }
        }

        $this->displayStats($stats, $dryRun);

        return Command::SUCCESS;
    }

    /**
     * Compress a log file
     */
    protected function compressFile(string $filePath, bool $dryRun): bool
    {
        if ($dryRun) {
            return true;
        }

        try {
            $compressedPath = $filePath . '.gz';
            
            // Read original file
            $data = file_get_contents($filePath);
            if ($data === false) {
                return false;
            }

            // Compress data
            $compressedData = gzencode($data, 9);
            if ($compressedData === false) {
                return false;
            }

            // Write compressed file
            if (file_put_contents($compressedPath, $compressedData) === false) {
                return false;
            }

            // Delete original file
            if (!unlink($filePath)) {
                // Clean up compressed file if original deletion fails
                unlink($compressedPath);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Compression error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine if a file should be deleted
     */
    protected function shouldDeleteFile(string $fileName, Carbon $fileTime, Carbon $cutoffDate): bool
    {
        // Always delete files older than cutoff
        if ($fileTime->isBefore($cutoffDate)) {
            return true;
        }

        // Delete compressed files that are older than cutoff
        if (str_ends_with($fileName, '.gz') && $fileTime->isBefore($cutoffDate)) {
            return true;
        }

        return false;
    }

    /**
     * Display cleanup statistics
     */
    protected function displayStats(array $stats, bool $dryRun): void
    {
        $this->newLine();
        $this->info("Cleanup Statistics:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Files Scanned', $stats['scanned']],
                ['Files Compressed', $stats['compressed']],
                ['Files Deleted', $stats['deleted']],
                ['Errors', $stats['errors']],
                ['Disk Space Freed', $this->formatBytes($stats['size_freed'])],
            ]
        );

        if ($stats['errors'] > 0) {
            $this->warn("Some operations failed. Check file permissions and available disk space.");
        }

        if ($dryRun) {
            $this->info("This was a dry run. No files were actually modified.");
            $this->info("Run without --dry-run to perform the cleanup.");
        } else {
            $this->info("Log cleanup completed successfully!");
        }
    }

    /**
     * Format bytes to human readable format
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
