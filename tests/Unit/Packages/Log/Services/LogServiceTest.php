<?php

namespace Tests\Unit\Packages\Log\Services;

use Tests\TestCase;
use Packages\Log\Services\LogService;
use Packages\Log\Services\LogFormatterService;
use Packages\Log\Services\QueryPerformanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogServiceTest extends TestCase
{
    protected LogService $logService;
    protected LogFormatterService $formatter;
    protected QueryPerformanceService $queryPerformance;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->formatter = new LogFormatterService();
        $this->queryPerformance = new QueryPerformanceService();
        $this->logService = new LogService($this->formatter, $this->queryPerformance);
    }

    /** @test */
    public function it_can_log_user_activity()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User Activity', \Mockery::type('array'))
            ->once();

        $this->logService->logUserActivity('test_action', 1, ['key' => 'value']);
        
        $this->assertTrue(true); // Test passes if no exceptions thrown
    }

    /** @test */
    public function it_can_log_api_request()
    {
        $request = Request::create('/test', 'GET');
        $response = new Response('test content', 200);
        $duration = 150.5;

        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('API Request', \Mockery::type('array'))
            ->once();

        $this->logService->logApiRequest($request, $response, $duration);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_errors()
    {
        $exception = new \Exception('Test exception');

        Log::shouldReceive('channel')
            ->with('error')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->with('Test exception', \Mockery::type('array'))
            ->once();

        $this->logService->logError($exception, ['context' => 'test']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_custom_events()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Custom Event', \Mockery::type('array'))
            ->once();

        $this->logService->logCustomEvent('test_event', ['data' => 'test']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_sql_queries()
    {
        config(['logging-package.sql.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('sql')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('debug')
            ->with('SQL Query', \Mockery::type('array'))
            ->once();

        $this->logService->logSqlQuery('SELECT * FROM users', 50.0, []);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_logs_slow_queries_as_warnings()
    {
        config(['logging-package.sql.enabled' => true]);
        config(['logging-package.sql.threshold_ms' => 100]);

        Log::shouldReceive('channel')
            ->with('sql')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->with('Slow Query Detected', \Mockery::type('array'))
            ->once();

        $this->logService->logSqlQuery('SELECT * FROM users', 200.0, []);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_sanitizes_sensitive_data()
    {
        config(['logging-package.requests.sensitive_fields' => ['password', 'token']]);

        $data = [
            'username' => 'john',
            'password' => 'secret123',
            'token' => 'abc123',
            'email' => 'john@example.com'
        ];

        $reflection = new \ReflectionClass($this->logService);
        $method = $reflection->getMethod('sanitizeData');
        $method->setAccessible(true);

        $result = $method->invoke($this->logService, $data);

        $this->assertEquals('john', $result['username']);
        $this->assertEquals('[REDACTED]', $result['password']);
        $this->assertEquals('[REDACTED]', $result['token']);
        $this->assertEquals('john@example.com', $result['email']);
    }

    /** @test */
    public function it_generates_request_id()
    {
        $requestId = $this->logService->getRequestId();
        
        $this->assertStringStartsWith('req_', $requestId);
        $this->assertEquals(14, strlen($requestId)); // 'req_' + 10 random chars
    }

    /** @test */
    public function it_skips_logging_when_disabled()
    {
        config(['logging-package.user_activity.enabled' => false]);

        Log::shouldNotReceive('channel');

        $this->logService->logUserActivity('test_action', 1, ['key' => 'value']);
        
        $this->assertTrue(true);
    }
}
