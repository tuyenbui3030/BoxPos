<?php

namespace Packages\Log\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\Log\Services\LogService;
use Symfony\Component\HttpFoundation\Response;

class LogPerformance
{
    protected LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('logging-package.performance.enabled', true)) {
            return $next($request);
        }

        // Record initial metrics
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $startPeakMemory = memory_get_peak_usage(true);

        // Process the request
        $response = $next($request);

        // Calculate performance metrics
        $metrics = $this->calculatePerformanceMetrics(
            $startTime,
            $startMemory,
            $startPeakMemory,
            $request,
            $response
        );

        // Log performance metrics
        $this->logService->logPerformance($metrics);

        return $response;
    }

    /**
     * Calculate performance metrics
     */
    protected function calculatePerformanceMetrics(
        float $startTime,
        int $startMemory,
        int $startPeakMemory,
        Request $request,
        Response $response
    ): array {
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $endPeakMemory = memory_get_peak_usage(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = $endMemory - $startMemory;
        $peakMemoryUsed = $endPeakMemory - $startPeakMemory;

        return [
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'ip' => $request->ip(),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'size_bytes' => strlen($response->getContent()),
            ],
            'performance' => [
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_bytes' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'peak_memory_used_bytes' => $peakMemoryUsed,
                'peak_memory_used_mb' => round($peakMemoryUsed / 1024 / 1024, 2),
                'memory_start_mb' => round($startMemory / 1024 / 1024, 2),
                'memory_end_mb' => round($endMemory / 1024 / 1024, 2),
            ],
            'thresholds' => [
                'execution_threshold_ms' => config('logging-package.performance.execution_threshold_ms', 1000),
                'memory_threshold_mb' => config('logging-package.performance.memory_threshold_mb', 100),
                'exceeds_execution_threshold' => $executionTime > config('logging-package.performance.execution_threshold_ms', 1000),
                'exceeds_memory_threshold' => ($endPeakMemory / 1024 / 1024) > config('logging-package.performance.memory_threshold_mb', 100),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
