<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup 
                            {--days=30 : Number of days to keep sessions}
                            {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions from database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        // Count sessions to be deleted
        $sessionsCount = DB::table('sessions')
            ->where('last_activity', '<', $cutoffDate->timestamp)
            ->count();
            
        if ($sessionsCount === 0) {
            $this->info('No expired sessions found.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$sessionsCount} expired sessions older than {$days} days.");
        
        if (!$force && !$this->confirm('Do you want to delete these sessions?')) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }
        
        // Delete expired sessions
        $deleted = DB::table('sessions')
            ->where('last_activity', '<', $cutoffDate->timestamp)
            ->delete();
            
        $this->info("Successfully deleted {$deleted} expired sessions.");
        
        // Also clean up old remember tokens (optional)
        if ($this->confirm('Do you want to clean up old remember tokens as well?')) {
            $this->cleanupRememberTokens($days);
        }
        
        return self::SUCCESS;
    }
    
    /**
     * Clean up old remember tokens
     */
    protected function cleanupRememberTokens(int $days): void
    {
        $cutoffDate = Carbon::now()->subDays($days);
        
        // This would depend on your user model structure
        // Assuming you have a last_login_at field
        $count = DB::table('users')
            ->whereNotNull('remember_token')
            ->where('last_login_at', '<', $cutoffDate)
            ->update(['remember_token' => null]);
            
        $this->info("Cleaned up {$count} old remember tokens.");
    }
}
