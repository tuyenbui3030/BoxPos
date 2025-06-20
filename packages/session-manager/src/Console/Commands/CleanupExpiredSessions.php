<?php

namespace Packages\SessionManager\Console\Commands;

use Illuminate\Console\Command;
use Packages\SessionManager\Services\SessionService;
use Packages\Log\Traits\Loggable;
use Carbon\Carbon;

class CleanupExpiredSessions extends Command
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'session:cleanup 
                           {--days=30 : Number of days to retain sessions}
                           {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired sessions from the database';

    /**
     * Session service instance
     *
     * @var SessionService
     */
    protected SessionService $sessionService;

    /**
     * Constructor
     *
     * @param SessionService $sessionService
     */
    public function __construct(SessionService $sessionService)
    {
        parent::__construct();
        $this->sessionService = $sessionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            $days = $this->option('days') ?: config('session-manager.cleanup.retention_days', 30);
            $dryRun = $this->option('dry-run');
            
            // ⚠️ MANDATORY: Log user activity for console command
            $this->logActivity('session_cleanup_command_started', [
                'retention_days' => $days,
                'dry_run' => $dryRun,
                'command' => $this->getName(),
                'action' => 'cleanup_sessions_command',
            ]);

            $this->info("Cleaning up sessions older than {$days} days");
            
            $result = $this->sessionService->cleanupExpiredSessions($days, $dryRun);
            
            if ($dryRun) {
                $count = $result['sessions_to_delete'];
                if ($count === 0) {
                    $this->info('No expired sessions found.');
                } else {
                    $this->info("Would delete {$count} expired sessions (dry run)");
                    $this->line("Cutoff date: {$result['cutoff_date']}");
                }
            } else {
                $count = $result['sessions_deleted'];
                if ($count === 0) {
                    $this->info('No expired sessions found.');
                } else {
                    $this->info("Successfully deleted {$count} expired sessions.");
                    $this->line("Cutoff date: {$result['cutoff_date']}");
                }
            }

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_cleanup_command', $startTime, [
                'retention_days' => $days,
                'dry_run' => $dryRun,
                'sessions_affected' => $dryRun ? ($result['sessions_to_delete'] ?? 0) : ($result['sessions_deleted'] ?? 0),
            ]);

            return 0;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'session_cleanup_command',
                'retention_days' => $days ?? null,
                'dry_run' => $dryRun ?? null,
                'command' => $this->getName(),
            ]);
            
            $this->error("Error cleaning up sessions: " . $e->getMessage());
            return 1;
        }
    }
}
