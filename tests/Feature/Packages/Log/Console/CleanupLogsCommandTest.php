<?php

namespace Tests\Feature\Packages\Log\Console;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class CleanupLogsCommandTest extends TestCase
{
    protected string $testLogPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testLogPath = storage_path('logs/test');
        
        // Create test directory
        if (!File::exists($this->testLogPath)) {
            File::makeDirectory($this->testLogPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (File::exists($this->testLogPath)) {
            File::deleteDirectory($this->testLogPath);
        }
        
        parent::tearDown();
    }

    /** @test */
    public function it_runs_cleanup_command_successfully()
    {
        $exitCode = Artisan::call('log:cleanup', ['--dry-run' => true]);
        
        $this->assertEquals(0, $exitCode);
    }

    /** @test */
    public function it_shows_dry_run_output()
    {
        // Create some test log files
        $this->createTestLogFile('old.log', 20); // 20 days old
        $this->createTestLogFile('recent.log', 5); // 5 days old

        $exitCode = Artisan::call('log:cleanup', [
            '--days' => 15,
            '--dry-run' => true
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContains('DRY RUN MODE', $output);
        $this->assertStringContains('Cleanup Statistics', $output);
    }

    /** @test */
    public function it_respects_retention_days_parameter()
    {
        // Create test files
        $oldFile = $this->createTestLogFile('old.log', 20);
        $recentFile = $this->createTestLogFile('recent.log', 5);

        $exitCode = Artisan::call('log:cleanup', [
            '--days' => 10, // Keep files newer than 10 days
            '--dry-run' => true
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContains('old.log', $output); // Should be marked for deletion
        $this->assertStringNotContains('recent.log', $output); // Should be kept
    }

    /** @test */
    public function it_shows_compression_in_dry_run()
    {
        $this->createTestLogFile('test.log', 20);

        $exitCode = Artisan::call('log:cleanup', [
            '--days' => 15,
            '--compress' => true,
            '--dry-run' => true
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContains('Compression: enabled', $output);
    }

    /** @test */
    public function it_displays_statistics_table()
    {
        $this->createTestLogFile('test1.log', 20);
        $this->createTestLogFile('test2.log', 25);

        $exitCode = Artisan::call('log:cleanup', [
            '--days' => 15,
            '--dry-run' => true
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContains('Cleanup Statistics:', $output);
        $this->assertStringContains('Files Scanned', $output);
        $this->assertStringContains('Files Deleted', $output);
        $this->assertStringContains('Disk Space Freed', $output);
    }

    /**
     * Create a test log file with specified age
     */
    protected function createTestLogFile(string $filename, int $daysOld): string
    {
        $filePath = $this->testLogPath . '/' . $filename;
        $content = "Test log content for {$filename}\n";
        
        File::put($filePath, $content);
        
        // Set the modification time to simulate old files
        $timestamp = Carbon::now()->subDays($daysOld)->timestamp;
        touch($filePath, $timestamp);
        
        return $filePath;
    }
}
