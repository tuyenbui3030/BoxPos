<?php

namespace Tests\Unit\Packages\Log\Services;

use Tests\TestCase;
use Packages\Log\Services\LogFormatterService;

class LogFormatterServiceTest extends TestCase
{
    protected LogFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new LogFormatterService();
    }

    /** @test */
    public function it_can_format_as_json()
    {
        $data = ['message' => 'test', 'level' => 'info'];
        
        $result = $this->formatter->formatAsJson($data);
        
        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('test', $decoded['message']);
        $this->assertEquals('info', $decoded['level']);
    }

    /** @test */
    public function it_can_format_as_structured_text()
    {
        $data = ['message' => 'test', 'level' => 'info', 'user_id' => 123];
        
        $result = $this->formatter->formatAsStructured($data);
        
        $this->assertStringContains('message: test', $result);
        $this->assertStringContains('level: info', $result);
        $this->assertStringContains('user_id: 123', $result);
        $this->assertStringContains('|', $result);
    }

    /** @test */
    public function it_can_format_as_single_line()
    {
        $data = ['message' => 'test message', 'level' => 'info', 'user_id' => 123];
        
        $result = $this->formatter->formatAsLine($data);
        
        $this->assertStringStartsWith('test message', $result);
        $this->assertStringContains('[level=info, user_id=123]', $result);
    }

    /** @test */
    public function it_can_format_sql_queries()
    {
        $query = 'SELECT * FROM users WHERE id = ?';
        $bindings = [123];
        $time = 45.67;

        $result = $this->formatter->formatSqlQuery($query, $bindings, $time);

        $this->assertEquals($query, $result['query']);
        $this->assertEquals($bindings, $result['bindings']);
        $this->assertEquals(45.67, $result['execution_time_ms']);
        $this->assertEquals('SELECT', $result['query_type']);
        $this->assertEquals('users', $result['table_name']);
    }

    /** @test */
    public function it_can_replace_sql_placeholders()
    {
        config(['logging-package.channels.sql.replace_placeholders' => true]);
        
        $query = 'SELECT * FROM users WHERE id = ? AND name = ?';
        $bindings = [123, 'John Doe'];

        $result = $this->formatter->formatSqlQuery($query, $bindings);

        $expected = "SELECT * FROM users WHERE id = 123 AND name = 'John Doe'";
        $this->assertEquals($expected, $result['formatted_query']);
    }

    /** @test */
    public function it_determines_query_types_correctly()
    {
        $queries = [
            'SELECT * FROM users' => 'SELECT',
            'INSERT INTO users (name) VALUES (?)' => 'INSERT',
            'UPDATE users SET name = ?' => 'UPDATE',
            'DELETE FROM users WHERE id = ?' => 'DELETE',
            'CREATE TABLE test (id INT)' => 'CREATE',
            'ALTER TABLE users ADD COLUMN email VARCHAR(255)' => 'ALTER',
            'DROP TABLE test' => 'DROP',
            'SHOW TABLES' => 'OTHER',
        ];

        $reflection = new \ReflectionClass($this->formatter);
        $method = $reflection->getMethod('determineSqlQueryType');
        $method->setAccessible(true);

        foreach ($queries as $query => $expectedType) {
            $result = $method->invoke($this->formatter, $query);
            $this->assertEquals($expectedType, $result, "Failed for query: {$query}");
        }
    }

    /** @test */
    public function it_extracts_table_names_correctly()
    {
        $queries = [
            'SELECT * FROM users' => 'users',
            'SELECT * FROM `customers`' => 'customers',
            'INSERT INTO orders (total) VALUES (100)' => 'orders',
            'UPDATE products SET price = 50' => 'products',
            'DELETE FROM sessions WHERE expired = 1' => 'sessions',
            'SELECT COUNT(*) as count' => null,
        ];

        $reflection = new \ReflectionClass($this->formatter);
        $method = $reflection->getMethod('extractTableName');
        $method->setAccessible(true);

        foreach ($queries as $query => $expectedTable) {
            $result = $method->invoke($this->formatter, $query);
            $this->assertEquals($expectedTable, $result, "Failed for query: {$query}");
        }
    }

    /** @test */
    public function it_formats_performance_metrics()
    {
        $metrics = [
            'execution_time' => 1500, // milliseconds
            'memory_usage' => 52428800, // bytes (50MB)
            'memory_peak' => 104857600, // bytes (100MB)
            'cpu_usage' => 75.5,
            'query_count' => 15,
            'slow_query_count' => 2,
        ];

        $result = $this->formatter->formatPerformanceMetrics($metrics);

        $this->assertEquals(1500, $result['execution_time_ms']);
        $this->assertEquals(50, $result['memory_usage_mb']);
        $this->assertEquals(100, $result['memory_peak_mb']);
        $this->assertEquals(75.5, $result['cpu_usage_percent']);
        $this->assertEquals(15, $result['query_count']);
        $this->assertEquals(2, $result['slow_query_count']);
    }

    /** @test */
    public function it_formats_user_activity()
    {
        $this->actingAs(factory(\App\Models\User::class)->create(['id' => 123]));
        
        $result = $this->formatter->formatUserActivity('login', 123, ['ip' => '127.0.0.1']);

        $this->assertEquals('login', $result['action']);
        $this->assertEquals(123, $result['user_id']);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('session_id', $result);
    }

    /** @test */
    public function it_formats_stack_trace()
    {
        $exception = new \Exception('Test exception');

        $result = $this->formatter->formatStackTrace($exception);

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result)); // Default max lines
        
        if (!empty($result)) {
            $this->assertArrayHasKey('file', $result[0]);
            $this->assertArrayHasKey('line', $result[0]);
            $this->assertArrayHasKey('function', $result[0]);
        }
    }
}
