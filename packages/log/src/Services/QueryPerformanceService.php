<?php

namespace Packages\Log\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Packages\Log\Services\SentryService;

class QueryPerformanceService
{
    protected array $queries = [];
    protected array $requestQueries = [];
    protected float $requestStartTime;
    protected bool $isTracking = false;
    protected string $requestId;
    protected ?SentryService $sentryService = null;

    public function __construct()
    {
        $this->requestId = 'req_' . Str::random(10);
        $this->requestStartTime = microtime(true);
        
        // Initialize Sentry service if enabled
        if (config('logging-package.sentry.enabled', false)) {
            $this->sentryService = app(SentryService::class);
        }
    }

    /**
     * Start tracking queries for the current request
     */
    public function startRequestTracking(): void
    {
        $this->isTracking = true;
        $this->queries = [];
        $this->requestStartTime = microtime(true);
    }

    /**
     * Stop tracking queries and return analysis
     */
    public function stopRequestTracking(): array
    {
        $this->isTracking = false;
        
        return $this->analyzeRequestQueries();
    }

    /**
     * Track a single query
     */
    public function trackQuery(string $sql, float $time, array $bindings = []): void
    {
        if (!$this->isTracking) {
            return;
        }

        $query = [
            'sql' => $sql,
            'time' => $time,
            'bindings' => $bindings,
            'timestamp' => microtime(true),
            'request_id' => $this->requestId,
            'query_hash' => $this->generateQueryHash($sql),
            'query_type' => $this->getQueryType($sql),
            'table_name' => $this->extractTableName($sql),
        ];

        $this->queries[] = $query;
    }

    /**
     * Analyze queries for the current request
     */
    public function analyzeRequestQueries(): array
    {
        if (empty($this->queries)) {
            return [];
        }

        $analysis = [
            'request_id' => $this->requestId,
            'total_queries' => count($this->queries),
            'total_time' => array_sum(array_column($this->queries, 'time')),
            'slow_queries' => $this->findSlowQueries(),
            'duplicate_queries' => $this->findDuplicateQueries(),
            'n_plus_one_patterns' => $this->detectNPlusOnePatterns(),
            'query_types' => $this->analyzeQueryTypes(),
            'table_usage' => $this->analyzeTableUsage(),
            'performance_warnings' => [],
        ];

        // Add performance warnings
        $analysis['performance_warnings'] = $this->generatePerformanceWarnings($analysis);

        // Log analysis if there are issues
        if (!empty($analysis['performance_warnings'])) {
            $this->logPerformanceWarnings($analysis);
        }

        return $analysis;
    }

    /**
     * Find slow queries based on threshold
     */
    protected function findSlowQueries(): array
    {
        $threshold = config('logging-package.sql.threshold_ms', 500);
        
        return array_filter($this->queries, function ($query) use ($threshold) {
            return $query['time'] > $threshold;
        });
    }

    /**
     * Find duplicate queries
     */
    protected function findDuplicateQueries(): array
    {
        $queryHashes = array_column($this->queries, 'query_hash');
        $duplicates = array_filter(array_count_values($queryHashes), function ($count) {
            return $count > 1;
        });

        return array_map(function ($hash) {
            return array_filter($this->queries, function ($query) use ($hash) {
                return $query['query_hash'] === $hash;
            });
        }, array_keys($duplicates));
    }

    /**
     * Detect N+1 query patterns
     */
    protected function detectNPlusOnePatterns(): array
    {
        $patterns = [];
        $queryGroups = collect($this->queries)->groupBy('query_hash');

        foreach ($queryGroups as $hash => $queries) {
            if (count($queries) > 5) { // Threshold for N+1 detection
                $firstQuery = $queries->first();
                
                // Check if it's a SELECT query with WHERE clause
                if ($this->isSelectQuery($firstQuery['sql']) && $this->hasWhereClause($firstQuery['sql'])) {
                    $patterns[] = [
                        'query_hash' => $hash,
                        'query' => $firstQuery['sql'],
                        'count' => count($queries),
                        'total_time' => $queries->sum('time'),
                        'table_name' => $firstQuery['table_name'],
                        'likely_n_plus_one' => true,
                    ];
                }
            }
        }

        return $patterns;
    }

    /**
     * Analyze query types distribution
     */
    protected function analyzeQueryTypes(): array
    {
        $types = collect($this->queries)->groupBy('query_type');
        
        return $types->map(function ($queries, $type) {
            return [
                'count' => count($queries),
                'total_time' => array_sum(array_column($queries->toArray(), 'time')),
                'avg_time' => array_sum(array_column($queries->toArray(), 'time')) / count($queries),
            ];
        })->toArray();
    }

    /**
     * Analyze table usage patterns
     */
    protected function analyzeTableUsage(): array
    {
        $tableUsage = collect($this->queries)
            ->filter(function ($query) {
                return !is_null($query['table_name']);
            })
            ->groupBy('table_name');

        return $tableUsage->map(function ($queries, $table) {
            return [
                'query_count' => count($queries),
                'total_time' => array_sum(array_column($queries->toArray(), 'time')),
                'query_types' => collect($queries)->groupBy('query_type')->keys()->toArray(),
            ];
        })->toArray();
    }

    /**
     * Generate performance warnings
     */
    protected function generatePerformanceWarnings(array $analysis): array
    {
        $warnings = [];

        // Too many queries
        $maxQueries = config('logging-package.sql.max_queries_per_request', 50);
        if ($analysis['total_queries'] > $maxQueries) {
            $warning = [
                'type' => 'too_many_queries',
                'message' => "Request executed {$analysis['total_queries']} queries (limit: {$maxQueries})",
                'severity' => 'high',
            ];
            $warnings[] = $warning;
            
            // Report to Sentry if enabled
            if ($this->sentryService) {
                $this->sentryService->reportMessage(
                    $warning['message'],
                    'warning',
                    $analysis,
                    ['performance_issue' => 'too_many_queries']
                );
            }
        }

        // Slow total time
        if ($analysis['total_time'] > 1000) { // 1 second
            $warning = [
                'type' => 'slow_total_time',
                'message' => "Total query time: {$analysis['total_time']}ms exceeds 1000ms",
                'severity' => 'medium',
            ];
            $warnings[] = $warning;
            
            // Report to Sentry if enabled
            if ($this->sentryService) {
                $this->sentryService->reportMessage(
                    $warning['message'],
                    'warning',
                    $analysis,
                    ['performance_issue' => 'slow_queries']
                );
            }
        }

        // N+1 patterns detected
        if (!empty($analysis['n_plus_one_patterns'])) {
            foreach ($analysis['n_plus_one_patterns'] as $pattern) {
                $warning = [
                    'type' => 'n_plus_one',
                    'message' => "Possible N+1 query on table '{$pattern['table_name']}' ({$pattern['count']} queries)",
                    'severity' => 'high',
                ];
                $warnings[] = $warning;
                
                // Report to Sentry if enabled
                if ($this->sentryService && config('logging-package.sentry.report_n_plus_one', true)) {
                    $this->sentryService->logNPlusOneQuery($pattern);
                }
            }
        }

        // Too many duplicates
        if (!empty($analysis['duplicate_queries'])) {
            foreach ($analysis['duplicate_queries'] as $duplicates) {
                $count = count($duplicates);
                if ($count > 3) {
                    $warning = [
                        'type' => 'duplicate_queries',
                        'message' => "Query executed {$count} times with same parameters",
                        'severity' => 'medium',
                    ];
                    $warnings[] = $warning;
                    
                    // Report to Sentry if enabled
                    if ($this->sentryService) {
                        $this->sentryService->reportMessage(
                            $warning['message'],
                            'warning',
                            ['duplicates' => $duplicates],
                            ['performance_issue' => 'duplicate_queries']
                        );
                    }
                }
            }
        }

        return $warnings;
    }

    /**
     * Log performance warnings
     */
    protected function logPerformanceWarnings(array $analysis): void
    {
        foreach ($analysis['performance_warnings'] as $warning) {
            $logLevel = $warning['severity'] === 'high' ? 'warning' : 'info';
            
            Log::channel('sql')->{$logLevel}('Query Performance Warning', [
                'request_id' => $this->requestId,
                'warning_type' => $warning['type'],
                'message' => $warning['message'],
                'severity' => $warning['severity'],
                'total_queries' => $analysis['total_queries'],
                'total_time_ms' => $analysis['total_time'],
            ]);
        }
    }

    /**
     * Generate query hash for duplicate detection
     */
    protected function generateQueryHash(string $sql): string
    {
        // Normalize query by removing parameter values and extra whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($sql));
        $normalized = preg_replace("/('[^']*'|\"[^\"]*\"|[0-9]+)/", '?', $normalized);
        
        return hash('sha256', strtolower($normalized));
    }

    /**
     * Get query type from SQL
     */
    protected function getQueryType(string $sql): string
    {
        $sql = strtoupper(trim($sql));
        
        if (str_starts_with($sql, 'SELECT')) return 'SELECT';
        if (str_starts_with($sql, 'INSERT')) return 'INSERT';
        if (str_starts_with($sql, 'UPDATE')) return 'UPDATE';
        if (str_starts_with($sql, 'DELETE')) return 'DELETE';
        if (str_starts_with($sql, 'CREATE')) return 'CREATE';
        if (str_starts_with($sql, 'ALTER')) return 'ALTER';
        if (str_starts_with($sql, 'DROP')) return 'DROP';
        
        return 'OTHER';
    }

    /**
     * Extract table name from SQL query
     */
    protected function extractTableName(string $sql): ?string
    {
        $sql = strtolower(trim($sql));
        
        // SELECT queries
        if (preg_match('/^select.*?from\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        // INSERT queries
        if (preg_match('/^insert\s+into\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        // UPDATE queries
        if (preg_match('/^update\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        // DELETE queries
        if (preg_match('/^delete\s+from\s+`?(\w+)`?/i', $sql, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Check if query is a SELECT query
     */
    protected function isSelectQuery(string $sql): bool
    {
        return str_starts_with(strtoupper(trim($sql)), 'SELECT');
    }

    /**
     * Check if query has WHERE clause
     */
    protected function hasWhereClause(string $sql): bool
    {
        return preg_match('/\bwhere\b/i', $sql) === 1;
    }

    /**
     * Get current request ID
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Get all tracked queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Get query statistics
     */
    public function getQueryStats(): array
    {
        return [
            'total_queries' => count($this->queries),
            'total_time' => array_sum(array_column($this->queries, 'time')),
            'avg_time' => count($this->queries) > 0 
                ? array_sum(array_column($this->queries, 'time')) / count($this->queries) 
                : 0,
            'slow_queries' => count($this->findSlowQueries()),
        ];
    }

    /**
     * Reset tracking data
     */
    public function reset(): void
    {
        $this->queries = [];
        $this->requestQueries = [];
        $this->isTracking = false;
        $this->requestId = 'req_' . Str::random(10);
        $this->requestStartTime = microtime(true);
    }
}
