<?php

namespace Packages\Common\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Packages\Common\Models\ActivityLog;
use Packages\Common\Models\ErrorLog;
use Packages\Common\Models\PerformanceLog;
use Packages\Common\Models\SecurityLog;
use Packages\Common\Services\LoggingService;
use Packages\Common\Tests\TestCase;

class LoggingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LoggingService $loggingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loggingService = new LoggingService();
    }

    public function test_can_log_activity(): void
    {
        $activity = 'test_activity';
        $context = ['key' => 'value'];

        $this->loggingService->logActivity($activity, $context);

        $this->assertDatabaseHas('activity_logs', [
            'activity' => $activity,
        ]);

        $log = ActivityLog::where('activity', $activity)->first();
        $this->assertEquals($context, $log->context);
    }

    public function test_can_log_error(): void
    {
        $exception = new \Exception('Test error message', 500);
        $context = ['additional' => 'context'];

        $this->loggingService->logError($exception, $context);

        $this->assertDatabaseHas('error_logs', [
            'error_type' => \Exception::class,
            'error_message' => 'Test error message',
            'error_code' => 500,
        ]);

        $log = ErrorLog::where('error_message', 'Test error message')->first();
        $this->assertEquals($context, $log->context);
    }

    public function test_can_log_performance(): void
    {
        $operation = 'test_operation';
        $duration = 1.5;
        $context = ['operation_data' => 'test'];

        $this->loggingService->logPerformance($operation, $duration, $context);

        $this->assertDatabaseHas('performance_logs', [
            'operation' => $operation,
            'duration' => $duration,
        ]);

        $log = PerformanceLog::where('operation', $operation)->first();
        $this->assertEquals($context, $log->context);
    }

    public function test_can_log_security_event(): void
    {
        $event = 'failed_login_attempt';
        $context = ['ip_address' => '192.168.1.1'];

        $this->loggingService->logSecurity($event, $context);

        $this->assertDatabaseHas('security_logs', [
            'event' => $event,
        ]);

        $log = SecurityLog::where('event', $event)->first();
        $this->assertEquals($context, $log->context);
    }

    public function test_can_log_business_event(): void
    {
        $event = 'order_created';
        $data = ['order_id' => 123];

        $this->loggingService->logBusinessEvent($event, $data);

        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'business_event_order_created',
        ]);
    }

    public function test_can_get_user_activity_logs(): void
    {
        $userId = 1;
        
        // Create some activity logs
        ActivityLog::create([
            'activity' => 'test_activity_1',
            'user_id' => $userId,
            'context' => [],
            'created_at' => now(),
        ]);
        
        ActivityLog::create([
            'activity' => 'test_activity_2',
            'user_id' => $userId,
            'context' => [],
            'created_at' => now(),
        ]);

        $logs = $this->loggingService->getUserActivityLogs($userId);

        $this->assertCount(2, $logs);
        $this->assertTrue($logs->every(fn($log) => $log->user_id === $userId));
    }

    public function test_can_get_store_error_logs(): void
    {
        $storeId = 1;
        
        // Create some error logs
        ErrorLog::create([
            'error_type' => \Exception::class,
            'error_message' => 'Test error 1',
            'error_code' => 500,
            'file' => 'test.php',
            'line' => 10,
            'stack_trace' => 'test trace',
            'store_id' => $storeId,
            'context' => [],
            'created_at' => now(),
        ]);

        $logs = $this->loggingService->getStoreErrorLogs($storeId);

        $this->assertCount(1, $logs);
        $this->assertEquals($storeId, $logs->first()->store_id);
    }

    public function test_can_get_performance_metrics(): void
    {
        $operation = 'test_operation';
        
        // Create performance logs
        PerformanceLog::create([
            'operation' => $operation,
            'duration' => 1.5,
            'memory_usage' => 1024,
            'peak_memory' => 2048,
            'context' => [],
            'created_at' => now(),
        ]);

        $metrics = $this->loggingService->getPerformanceMetrics($operation);

        $this->assertCount(1, $metrics);
        $this->assertEquals($operation, $metrics->first()->operation);
    }

    public function test_can_clean_old_logs(): void
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

        $this->loggingService->cleanOldLogs();

        // Old logs should be deleted
        $this->assertDatabaseMissing('activity_logs', [
            'activity' => 'old_activity',
        ]);

        $this->assertDatabaseMissing('error_logs', [
            'error_message' => 'Old error',
        ]);

        // Recent logs should remain
        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'recent_activity',
        ]);
    }

    public function test_logs_to_laravel_log_on_database_failure(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Failed to log activity to database', \Mockery::type('array'));

        // Mock database failure by using invalid data
        $this->loggingService->logActivity('test', ['invalid' => new \stdClass()]);
    }
}