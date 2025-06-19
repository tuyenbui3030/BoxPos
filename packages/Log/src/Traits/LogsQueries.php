<?php

namespace Packages\Log\Traits;

use Packages\Log\Services\QueryPerformanceService;
use Packages\Log\Services\LogService;

trait LogsQueries
{
    /**
     * Execute a callable with query logging
     */
    protected function withQueryLogging(string $operation, callable $callback)
    {
        if (!config('logging-package.sql.enabled', false)) {
            return $callback();
        }

        $queryPerformanceService = app(QueryPerformanceService::class);
        $logService = app(LogService::class);

        // Start tracking
        $queryPerformanceService->startRequestTracking();
        $startTime = microtime(true);

        try {
            $result = $callback();
            
            // Stop tracking and get analysis
            $analysis = $queryPerformanceService->stopRequestTracking();
            $duration = (microtime(true) - $startTime) * 1000;

            // Log the operation with query metrics
            $this->logQueryOperation($operation, $analysis, $duration, true);

            return $result;
        } catch (\Throwable $e) {
            // Log failed operation
            $analysis = $queryPerformanceService->stopRequestTracking();
            $duration = (microtime(true) - $startTime) * 1000;
            
            $this->logQueryOperation($operation, $analysis, $duration, false, $e);
            
            throw $e;
        }
    }

    /**
     * Execute a database transaction with query logging
     */
    protected function withQueryLoggingTransaction(string $operation, callable $callback)
    {
        return \DB::transaction(function () use ($operation, $callback) {
            return $this->withQueryLogging($operation, $callback);
        });
    }

    /**
     * Log query operation results
     */
    protected function logQueryOperation(
        string $operation, 
        array $analysis, 
        float $duration, 
        bool $success, 
        ?\Throwable $exception = null
    ): void {
        $logService = app(LogService::class);

        $data = [
            'operation' => $operation,
            'success' => $success,
            'duration_ms' => round($duration, 2),
            'query_stats' => [
                'total_queries' => $analysis['total_queries'] ?? 0,
                'total_query_time_ms' => $analysis['total_time'] ?? 0,
                'slow_queries' => count($analysis['slow_queries'] ?? []),
                'duplicate_queries' => count($analysis['duplicate_queries'] ?? []),
                'n_plus_one_detected' => !empty($analysis['n_plus_one_patterns']),
            ],
            'performance_warnings' => $analysis['performance_warnings'] ?? [],
            'class' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? 'unknown',
        ];

        if ($exception) {
            $data['error'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        $logService->logCustomEvent('query_operation', $data);
    }

    /**
     * Log slow query warning
     */
    protected function logSlowQueryWarning(string $operation, array $slowQueries): void
    {
        $logService = app(LogService::class);

        foreach ($slowQueries as $query) {
            $logService->logCustomEvent('slow_query_warning', [
                'operation' => $operation,
                'query' => $query['sql'],
                'time_ms' => $query['time'],
                'table_name' => $query['table_name'] ?? 'unknown',
                'class' => static::class,
            ]);
        }
    }

    /**
     * Get query statistics for current operation
     */
    protected function getQueryStats(): array
    {
        $queryPerformanceService = app(QueryPerformanceService::class);
        return $queryPerformanceService->getQueryStats();
    }

    /**
     * Check if current operation has performance issues
     */
    protected function hasPerformanceIssues(): bool
    {
        $stats = $this->getQueryStats();
        
        $maxQueries = config('logging-package.sql.max_queries_per_request', 50);
        $maxTotalTime = config('logging-package.sql.max_total_time_ms', 1000);
        
        return $stats['total_queries'] > $maxQueries || 
               $stats['total_time'] > $maxTotalTime ||
               $stats['slow_queries'] > 0;
    }

    /**
     * Execute with query count limit
     */
    protected function withQueryLimit(int $maxQueries, callable $callback)
    {
        $queryPerformanceService = app(QueryPerformanceService::class);
        $queryPerformanceService->startRequestTracking();

        try {
            $result = $callback();
            
            $stats = $queryPerformanceService->getQueryStats();
            
            if ($stats['total_queries'] > $maxQueries) {
                $logService = app(LogService::class);
                $logService->logCustomEvent('query_limit_exceeded', [
                    'max_queries' => $maxQueries,
                    'actual_queries' => $stats['total_queries'],
                    'class' => static::class,
                    'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
                ]);
            }

            return $result;
        } finally {
            $queryPerformanceService->stopRequestTracking();
        }
    }

    /**
     * Execute with automatic N+1 detection
     */
    protected function withNPlusOneDetection(callable $callback)
    {
        if (!config('logging-package.sql.detect_n_plus_one', true)) {
            return $callback();
        }

        return $this->withQueryLogging('n_plus_one_check', function () use ($callback) {
            return $callback();
        });
    }

    /**
     * Helper to log query performance for model operations
     */
    protected function logModelQueryPerformance(string $modelClass, string $operation, callable $callback)
    {
        return $this->withQueryLogging("{$modelClass}_{$operation}", $callback);
    }
}
