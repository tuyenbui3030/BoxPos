<?php

namespace Tests\Unit\Packages\Log\Services;

use Tests\TestCase;
use Packages\Log\Services\QueryPerformanceService;
use Illuminate\Support\Facades\Log;

class QueryPerformanceServiceTest extends TestCase
{
    protected QueryPerformanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QueryPerformanceService();
    }

    /** @test */
    public function it_can_start_and_stop_tracking()
    {
        $this->service->startRequestTracking();
        
        // Track some queries
        $this->service->trackQuery('SELECT * FROM users', 50.0);
        $this->service->trackQuery('SELECT * FROM orders', 75.0);
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertEquals(2, $analysis['total_queries']);
        $this->assertEquals(125.0, $analysis['total_time']);
    }

    /** @test */
    public function it_tracks_queries_correctly()
    {
        $this->service->startRequestTracking();
        
        $this->service->trackQuery('SELECT * FROM users WHERE id = ?', 45.5, [123]);
        
        $queries = $this->service->getQueries();
        
        $this->assertCount(1, $queries);
        $this->assertEquals('SELECT * FROM users WHERE id = ?', $queries[0]['sql']);
        $this->assertEquals(45.5, $queries[0]['time']);
        $this->assertEquals([123], $queries[0]['bindings']);
        $this->assertEquals('SELECT', $queries[0]['query_type']);
        $this->assertEquals('users', $queries[0]['table_name']);
    }

    /** @test */
    public function it_detects_slow_queries()
    {
        config(['logging-package.sql.threshold_ms' => 100]);
        
        $this->service->startRequestTracking();
        $this->service->trackQuery('SELECT * FROM users', 50.0); // Fast
        $this->service->trackQuery('SELECT * FROM orders', 150.0); // Slow
        $this->service->trackQuery('SELECT * FROM products', 200.0); // Slow
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertCount(2, $analysis['slow_queries']);
    }

    /** @test */
    public function it_detects_duplicate_queries()
    {
        $this->service->startRequestTracking();
        
        // Same query structure, different bindings
        $this->service->trackQuery('SELECT * FROM users WHERE id = ?', 25.0, [1]);
        $this->service->trackQuery('SELECT * FROM users WHERE id = ?', 30.0, [2]);
        $this->service->trackQuery('SELECT * FROM users WHERE id = ?', 28.0, [3]);
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertNotEmpty($analysis['duplicate_queries']);
    }

    /** @test */
    public function it_detects_potential_n_plus_one_patterns()
    {
        $this->service->startRequestTracking();
        
        // Simulate N+1 pattern
        for ($i = 1; $i <= 10; $i++) {
            $this->service->trackQuery('SELECT * FROM orders WHERE user_id = ?', 20.0, [$i]);
        }
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertNotEmpty($analysis['n_plus_one_patterns']);
        $this->assertEquals(10, $analysis['n_plus_one_patterns'][0]['count']);
    }

    /** @test */
    public function it_analyzes_query_types()
    {
        $this->service->startRequestTracking();
        
        $this->service->trackQuery('SELECT * FROM users', 25.0);
        $this->service->trackQuery('SELECT * FROM orders', 30.0);
        $this->service->trackQuery('INSERT INTO logs (message) VALUES (?)', 15.0, ['test']);
        $this->service->trackQuery('UPDATE users SET last_login = NOW()', 20.0);
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertEquals(2, $analysis['query_types']['SELECT']['count']);
        $this->assertEquals(1, $analysis['query_types']['INSERT']['count']);
        $this->assertEquals(1, $analysis['query_types']['UPDATE']['count']);
    }

    /** @test */
    public function it_generates_performance_warnings()
    {
        config(['logging-package.sql.max_queries_per_request' => 5]);
        
        $this->service->startRequestTracking();
        
        // Add more queries than allowed
        for ($i = 0; $i < 8; $i++) {
            $this->service->trackQuery('SELECT * FROM users', 50.0);
        }
        
        $analysis = $this->service->stopRequestTracking();
        
        $this->assertNotEmpty($analysis['performance_warnings']);
        
        $tooManyQueriesWarning = collect($analysis['performance_warnings'])
            ->firstWhere('type', 'too_many_queries');
            
        $this->assertNotNull($tooManyQueriesWarning);
        $this->assertEquals('high', $tooManyQueriesWarning['severity']);
    }

    /** @test */
    public function it_logs_performance_warnings()
    {
        config(['logging-package.sql.max_queries_per_request' => 2]);
        
        Log::shouldReceive('channel')
            ->with('sql')
            ->andReturnSelf();
            
        Log::shouldReceive('warning')
            ->once()
            ->with('Query Performance Warning', \Mockery::type('array'));
        
        $this->service->startRequestTracking();
        
        // Add more queries than allowed to trigger warning
        for ($i = 0; $i < 5; $i++) {
            $this->service->trackQuery('SELECT * FROM users', 50.0);
        }
        
        $this->service->stopRequestTracking();
    }

    /** @test */
    public function it_provides_query_statistics()
    {
        $this->service->startRequestTracking();
        
        $this->service->trackQuery('SELECT * FROM users', 25.0);
        $this->service->trackQuery('SELECT * FROM orders', 150.0); // Slow query
        $this->service->trackQuery('INSERT INTO logs (message) VALUES (?)', 15.0);
        
        $stats = $this->service->getQueryStats();
        
        $this->assertEquals(3, $stats['total_queries']);
        $this->assertEquals(190.0, $stats['total_time']);
        $this->assertEquals(63.33, round($stats['avg_time'], 2));
        $this->assertEquals(1, $stats['slow_queries']); // One query > 100ms threshold
    }

    /** @test */
    public function it_can_reset_tracking_data()
    {
        $this->service->startRequestTracking();
        $this->service->trackQuery('SELECT * FROM users', 25.0);
        
        $this->assertCount(1, $this->service->getQueries());
        
        $this->service->reset();
        
        $this->assertCount(0, $this->service->getQueries());
    }

    /** @test */
    public function it_generates_unique_request_ids()
    {
        $service1 = new QueryPerformanceService();
        $service2 = new QueryPerformanceService();
        
        $this->assertNotEquals($service1->getRequestId(), $service2->getRequestId());
        $this->assertStringStartsWith('req_', $service1->getRequestId());
        $this->assertStringStartsWith('req_', $service2->getRequestId());
    }
}
