<?php

namespace Tests\Unit\Packages\Log\Traits;

use Tests\TestCase;
use Packages\Log\Traits\Loggable;
use Packages\Log\Services\LogService;
use Illuminate\Support\Facades\Log;

class LoggableTest extends TestCase
{
    use Loggable;

    /** @test */
    public function it_can_log_activity()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User Activity', \Mockery::type('array'))
            ->once();

        $this->logActivity('test_action', ['key' => 'value']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_events()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Custom Event', \Mockery::type('array'))
            ->once();

        $this->logEvent('test_event', ['data' => 'test']);
        
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

        $this->logError($exception, ['context' => 'test']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_with_context()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User Activity', \Mockery::on(function ($context) {
                return isset($context['data']['context']['class']) &&
                       $context['data']['context']['class'] === static::class;
            }))
            ->once();

        $this->logWithContext('test_action', ['key' => 'value']);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_model_events()
    {
        $model = new class {
            public function getKey() { return 123; }
            public function getAttributes() { return ['name' => 'test']; }
        };

        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User Activity', \Mockery::on(function ($context) {
                return $context['action'] === 'model_created' &&
                       isset($context['data']['model_id']) &&
                       $context['data']['model_id'] === 123;
            }))
            ->once();

        $this->logModelEvent('created', $model);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_log_process_steps()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('User Activity', \Mockery::on(function ($context) {
                return $context['action'] === 'order_processing_payment' &&
                       $context['data']['process'] === 'order_processing' &&
                       $context['data']['step'] === 'payment';
            }))
            ->once();

        $this->logProcessStep('order_processing', 'payment', ['amount' => 100]);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_measure_performance()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Custom Event', \Mockery::on(function ($context) {
                return $context['event'] === 'operation_performance' &&
                       isset($context['data']['duration_ms']) &&
                       is_numeric($context['data']['duration_ms']);
            }))
            ->once();

        $result = $this->withPerformanceLogging('test_operation', function () {
            usleep(1000); // Sleep for 1ms
            return 'test_result';
        });

        $this->assertEquals('test_result', $result);
    }

    /** @test */
    public function it_logs_performance_on_exception()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Custom Event', \Mockery::on(function ($context) {
                return $context['event'] === 'operation_performance' &&
                       isset($context['data']['error']) &&
                       $context['data']['error'] === true &&
                       $context['data']['exception'] === 'Exception';
            }))
            ->once();

        $this->expectException(\Exception::class);

        $this->withPerformanceLogging('test_operation', function () {
            throw new \Exception('Test exception');
        });
    }
}
