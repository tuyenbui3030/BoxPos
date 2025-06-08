<?php

namespace Packages\SessionManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredSessions extends Command
{
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
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!config('session-manager.cleanup.enabled', true)) {
            $this->info('Session cleanup is disabled.');
            return 0;
        }

        $days = $this->option('days') ?: config('session-manager.cleanup.retention_days', 30);
        $dryRun = $this->option('dry-run');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up sessions older than {$days} days (before {$cutoffDate->toDateTimeString()})");
        
        try {
            $query = DB::table('sessions')
                ->where('last_activity', '<', $cutoffDate->timestamp);
            
            $count = $query->count();
            
            if ($count === 0) {
                $this->info('No expired sessions found.');
                return 0;
            }
            
            if ($dryRun) {
                $this->info("Would delete {$count} expired sessions (dry run)");
                
                // Show sample sessions
                $samples = $query->limit(5)->get(['id', 'user_id', 'last_activity']);
                if ($samples->count() > 0) {
                    $this->table(
                        ['Session ID', 'User ID', 'Last Activity'],
                        $samples->map(function ($session) {
                            return [
                                substr($session->id, 0, 8) . '...',
                                $session->user_id ?? 'guest',
                                Carbon::createFromTimestamp($session->last_activity)->toDateTimeString()
                            ];
                        })->toArray()
                    );
                }
            } else {
                if ($this->confirm("Delete {$count} expired sessions?")) {
                    $deleted = $query->delete();
                    $this->info("Successfully deleted {$deleted} expired sessions.");
                } else {
                    $this->info('Operation cancelled.');
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error cleaning up sessions: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
