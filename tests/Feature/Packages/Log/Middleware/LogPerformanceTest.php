<?php

namespace Tests\Feature\Packages\Log\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Packages\Log\Middleware\LogPerformance;

class LogPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_performance_metrics_when_enabled()
    {
        config(['logging-package.performance.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('performance')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Performance Metrics', \Mockery::type('array'))
            ->once();

        $response = $this->get('/');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_skips_logging_when_disabled()
    {
        config(['logging-package.performance.enabled' => false]);

        Log::shouldNotReceive('channel');

        $response = $this->get('/');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_logs_execution_time()
    {
        config(['logging-package.performance.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('performance')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Performance Metrics', \Mockery::on(function ($context) {
                return isset($context['performance']['execution_time_ms']) && 
                       is_numeric($context['performance']['execution_time_ms']);
            }))
            ->once();

        $this->get('/');
    }

    /** @test */
    public function it_logs_memory_usage()
    {
        config(['logging-package.performance.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('performance')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('Performance Metrics', \Mockery::on(function ($context) {
                return isset($context['performance']['memory_used_mb']) && 
                       is_numeric($context['performance']['memory_used_mb']) &&
                       isset($context['performance']['peak_memory_used_mb']) &&
                       is_numeric($context['performance']['peak_memory_used_mb']);
            }))
            ->once();

        $this->get('/');
    }

    /** @test */
    public function it_detects_threshold_violations()
    {
        config([
            'logging-package.performance.enabled' => true,
            'logging-package.performance.execution_threshold_ms' => 1, // Very low threshold
            'logging-package.performance.memory_threshold_mb' => 1, // Very low threshold
        ]);

        Log::shouldReceive('channel')
            ->with('performance')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('warning')
            ->with('Performance Metrics', \Mockery::on(function ($context) {
                return isset($context['thresholds']['exceeds_execution_threshold']) ||
                       isset($context['thresholds']['exceeds_memory_threshold']);
            }))
            ->once();

        $this->get('/');
    }
}
