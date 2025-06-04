<?php

namespace Packages\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Too many authentication attempts. Please try again later.',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $response = $next($request);

        // Only increment rate limit for failed authentication attempts
        if ($this->isFailedAuthenticationAttempt($response)) {
            RateLimiter::hit($key, $decayMinutes * 60);
        } else {
            // Clear rate limit on successful authentication
            RateLimiter::clear($key);
        }

        $remaining = max(0, $maxAttempts - RateLimiter::attempts($key));

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remaining,
        ]);
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $email = $request->input('email', 'unknown');
        $ip = $request->ip();

        return "auth_rate_limit:{$email}:{$ip}";
    }

    /**
     * Determine if the response indicates a failed authentication attempt.
     */
    protected function isFailedAuthenticationAttempt(Response $response): bool
    {
        return $response->getStatusCode() === 401 || 
               $response->getStatusCode() === 422 || 
               $response->getStatusCode() === 403;
    }
}
