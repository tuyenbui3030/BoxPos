<?php

namespace Packages\Log\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Packages\Log\Services\LogService;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
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
        // Skip logging for excluded paths
        if ($this->shouldSkipLogging($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        // Log the request
        $this->logService->logApiRequest($request, $response, $duration);
        
        return $response;
    }

    /**
     * Check if logging should be skipped for this request
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        if (!config('logging-package.requests.enabled', true)) {
            return true;
        }

        $excludePaths = config('logging-package.requests.exclude_paths', []);
        $path = $request->path();

        foreach ($excludePaths as $excludePath) {
            if (str_starts_with($path, trim($excludePath, '/'))) {
                return true;
            }
        }

        return false;
    }
}
