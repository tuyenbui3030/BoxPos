<?php

namespace Packages\Customer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogCustomerActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        
        // Log the incoming request
        $this->logRequest($request);
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        
        // Log the response
        $this->logResponse($request, $response, $duration);
        
        return $response;
    }

    /**
     * Log the incoming request.
     */
    protected function logRequest(Request $request): void
    {
        Log::info('Customer API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'customer_id' => $request->route('customer') ?? $request->route('id'),
            'payload_size' => strlen($request->getContent()),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log the response.
     */
    protected function logResponse(Request $request, Response $response, float $duration): void
    {
        Log::info('Customer API Response', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'response_size' => strlen($response->getContent()),
            'user_id' => auth()->id(),
            'customer_id' => $request->route('customer') ?? $request->route('id'),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
