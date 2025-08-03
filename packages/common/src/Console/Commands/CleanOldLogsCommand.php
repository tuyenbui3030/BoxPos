<?php

namespace Packages\Common\Console\Commands;

use Illuminate\Console\Command;
use Packages\Common\Services\LoggingService;

class CleanOldLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'common:clean-logs 
                            {--days= : Number of days to retain logs (default from config)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean old log entries based on retention policy';

    protected LoggingService $loggingService;

    /**
     * Create a new command instance.
     */
    public function __construct(LoggingService $loggingService)
    {
        parent::__construct();
        $this->loggingService = $loggingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDays = $this->option('days') ?? config('common.logging.retention_days', 90);
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning logs older than {$retentionDays} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
            
            $cutoffDate = now()->subDays($retentionDays);
            
            $activityCount = \Packages\Common\Models\ActivityLog::where('created_at', '<', $cutoffDate)->count();
            $errorCount = \Packages\Common\Models\ErrorLog::where('created_at', '<', $cutoffDate)->count();
            $performanceCount = \Packages\Common\Models\PerformanceLog::where('created_at', '<', $cutoffDate)->count();
            $securityCount = \Packages\Common\Models\SecurityLog::where('created_at', '<', $cutoffDate)->count();
            
            $this->table(['Log Type', 'Records to Delete'], [
                ['Activity Logs', $activityCount],
                ['Error Logs', $errorCount],
                ['Performance Logs', $performanceCount],
                ['Security Logs', $securityCount],
                ['Total', $activityCount + $errorCount + $performanceCount + $securityCount],
            ]);
            
            return 0;
        }

        try {
            $this->loggingService->cleanOldLogs();
            $this->info('Log cleanup completed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Log cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}