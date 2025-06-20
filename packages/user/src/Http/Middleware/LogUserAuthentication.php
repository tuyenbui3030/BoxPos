<?php

namespace Packages\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        
        // Log the authentication attempt
        $this->logAuthenticationAttempt($request);
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        
        // Log the authentication result
        $this->logAuthenticationResult($request, $response, $duration);
        
        return $response;
    }

    /**
     * Log the authentication attempt.
     */
    protected function logAuthenticationAttempt(Request $request): void
    {
        $routeName = $request->route() ? $request->route()->getName() : 'unknown';
        
        Log::info('User Authentication Attempt', [
            'route' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
            'email' => $request->input('email'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log the authentication result.
     */
    protected function logAuthenticationResult(Request $request, Response $response, float $duration): void
    {
        $routeName = $request->route() ? $request->route()->getName() : 'unknown';
        $statusCode = $response->getStatusCode();
        $isSuccess = $statusCode >= 200 && $statusCode < 300;
        
        Log::info('User Authentication Result', [
            'route' => $routeName,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $statusCode,
            'success' => $isSuccess,
            'duration_ms' => round($duration * 1000, 2),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
            'user_id' => auth()->id(),
            'email' => $request->input('email'),
            'timestamp' => now()->toISOString(),
        ]);

        // Log failed authentication attempts separately for security monitoring
        if (!$isSuccess && in_array($routeName, ['login', 'register', 'password.update'])) {
            Log::warning('Failed Authentication Attempt', [
                'route' => $routeName,
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'user_agent' => $request->userAgent(),
                'status' => $statusCode,
                'timestamp' => now()->toISOString(),
            ]);
        }
    }
}
