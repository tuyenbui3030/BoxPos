<?php

namespace Packages\Log\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\Log\Services\LogService;
use Packages\Log\Services\QueryPerformanceService;
use Symfony\Component\HttpFoundation\Response;

class LogSqlQueries
{
    protected LogService $logService;
    protected QueryPerformanceService $queryPerformanceService;

    public function __construct(
        LogService $logService,
        QueryPerformanceService $queryPerformanceService
    ) {
        $this->logService = $logService;
        $this->queryPerformanceService = $queryPerformanceService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('logging-package.sql.enabled', false)) {
            return $next($request);
        }

        // Start query tracking
        $this->queryPerformanceService->startRequestTracking();
        
        // Process the request
        $response = $next($request);
        
        // Stop query tracking and analyze
        $analysis = $this->queryPerformanceService->stopRequestTracking();
        
        // Log query analysis if there are performance issues
        if (!empty($analysis['performance_warnings'])) {
            $this->logQueryAnalysis($request, $analysis);
        }
        
        return $response;
    }

    /**
     * Log query analysis results
     */
    protected function logQueryAnalysis(Request $request, array $analysis): void
    {
        $context = [
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
            ],
            'query_analysis' => $analysis,
        ];

        $this->logService->logCustomEvent('sql_query_analysis', $context);
    }
}
