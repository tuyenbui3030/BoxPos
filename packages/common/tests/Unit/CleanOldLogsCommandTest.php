<?php

namespace Packages\Common\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Packages\Common\Console\Commands\CleanOldLogsCommand;
use Packages\Common\Models\ActivityLog;
use Packages\Common\Models\ErrorLog;
use Packages\Common\Models\PerformanceLog;
use Packages\Common\Models\SecurityLog;
use Packages\Common\Services\LoggingService;
use Packages\Common\Tests\TestCase;

class CleanOldLogsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected LoggingService $loggingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loggingService = new LoggingService();
    }

    public function test_dry_run_shows_records_to_delete_without_deleting(): void
    {
        // Create old logs
        $oldDate = now()->subDays(100);
        
        ActivityLog::create([
            'activity' => 'old_activity',
            'context' => [],
            'created_at' => $oldDate,
        ]);

        ErrorLog::create([
            'error_type' => \Exception::class,
            'error_message' => 'Old error',
            'error_code' => 500,
            'file' => 'test.php',
            'line' => 10,
            'stack_trace' => 'test trace',
            'context' => [],
            'created_at' => $oldDate,
        ]);

        // Create recent logs
        ActivityLog::create([
            'activity' => 'recent_activity',
            'context' => [],
            'created_at' => now(),
        ]);

        $this->artisan('common:clean-logs', ['--dry-run' => true])
            ->expectsOutput('DRY RUN MODE - No data will be deleted')
            ->assertExitCode(0);

        // Verify no data was deleted
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'old_activity',
        ]);

        $this->assertDatabaseHas('error_logs', [
            'error_message' => 'Old error',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'recent_activity',
        ]);
    }

    public function test_command_deletes_old_logs(): void
    {
        // Create old logs
        $oldDate = now()->subDays(100);
        
        ActivityLog::create([
            'activity' => 'old_activity',
            'context' => [],
            'created_at' => $oldDate,
        ]);

        ErrorLog::create([
            'error_type' => \Exception::class,
            'error_message' => 'Old error',
            'error_code' => 500,
            'file' => 'test.php',
            'line' => 10,
            'stack_trace' => 'test trace',
            'context' => [],
            'created_at' => $oldDate,
        ]);

        // Create recent logs
        ActivityLog::create([
            'activity' => 'recent_activity',
            'context' => [],
            'created_at' => now(),
        ]);

        $this->artisan('common:clean-logs')
            ->expectsOutput('Log cleanup completed successfully.')
            ->assertExitCode(0);

        // Verify old data was deleted
        $this->assertDatabaseMissing('activity_logs', [
            'activity' => 'old_activity',
        ]);

        $this->assertDatabaseMissing('error_logs', [
            'error_message' => 'Old error',
        ]);

        // Verify recent data remains
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'recent_activity',
        ]);
    }

    public function test_command_accepts_custom_retention_days(): void
    {
        // Create logs that are 50 days old
        $oldDate = now()->subDays(50);
        
        ActivityLog::create([
            'activity' => 'old_activity',
            'context' => [],
            'created_at' => $oldDate,
        ]);

        // With default retention (90 days), this should not be deleted
        $this->artisan('common:clean-logs')
            ->assertExitCode(0);

        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'old_activity',
        ]);

        // With custom retention (30 days), this should be deleted
        $this->artisan('common:clean-logs', ['--days' => 30])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('activity_logs', [
            'activity' => 'old_activity',
        ]);
    }

    public function test_command_handles_exceptions_gracefully(): void
    {
        // Mock the logging service to throw an exception
        $mockLoggingService = $this->createMock(LoggingService::class);
        $mockLoggingService->method('cleanOldLogs')
            ->willThrowException(new \Exception('Database error'));

        $this->app->instance(LoggingService::class, $mockLoggingService);

        $this->artisan('common:clean-logs')
            ->expectsOutput('Log cleanup failed: Database error')
            ->assertExitCode(1);
    }
}