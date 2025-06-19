<?php

namespace Tests\Feature\Packages\Log\Middleware;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Packages\Log\Middleware\LogRequests;
use Packages\Log\Services\LogService;

class LogRequestsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_requests_when_enabled()
    {
        config(['logging-package.requests.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('API Request', \Mockery::type('array'))
            ->once();

        $response = $this->get('/');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_skips_logging_when_disabled()
    {
        config(['logging-package.requests.enabled' => false]);

        Log::shouldNotReceive('channel');

        $response = $this->get('/');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_skips_excluded_paths()
    {
        config([
            'logging-package.requests.enabled' => true,
            'logging-package.requests.exclude_paths' => ['/health', '/ping']
        ]);

        Log::shouldNotReceive('channel');

        // These paths should be excluded
        $this->get('/health');
        $this->get('/ping');
    }

    /** @test */
    public function it_logs_request_duration()
    {
        config(['logging-package.requests.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('API Request', \Mockery::on(function ($context) {
                return isset($context['duration_ms']) && is_numeric($context['duration_ms']);
            }))
            ->once();

        $this->get('/');
    }

    /** @test */
    public function it_includes_response_status_in_log()
    {
        config(['logging-package.requests.enabled' => true]);

        Log::shouldReceive('channel')
            ->with('daily')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('API Request', \Mockery::on(function ($context) {
                return isset($context['response_status']) && $context['response_status'] === 200;
            }))
            ->once();

        $this->get('/');
    }
}
