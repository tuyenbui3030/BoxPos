<?php

namespace Packages\Log\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\Log\Services\SentryService;
use Sentry\Laravel\Facade as Sentry;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;

/**
 * Middleware for Sentry performance monitoring integration
 * 
 * This middleware integrates Sentry performance monitoring with the existing
 * log package, providing detailed transaction tracking and performance insights.
 */
class SentryPerformanceMiddleware
{
    protected SentryService $sentryService;
    protected ?Transaction $transaction = null;

    public function __construct(SentryService $sentryService)
    {
        $this->sentryService = $sentryService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only enable for non-local environments or when explicitly enabled
        if (!$this->shouldTrackPerformance()) {
            return $next($request);
        }

        // Start Sentry transaction
        $transactionName = $this->getTransactionName($request);
        $context = new TransactionContext($transactionName, 'http.request');
        $context->setData([
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
        ]);
        
        $this->transaction = Sentry::startTransaction($context);

        // Set user context
        $this->setUserContext($request);

        // Add request breadcrumb
        $this->sentryService->addBreadcrumb(
            message: "Request started: {$request->method()} {$request->path()}",
            category: 'request',
            level: 'info',
            data: [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $response = $next($request);

            // Calculate performance metrics
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            // Set response context
            $this->sentryService->setContext('response', [
                'status_code' => $response->getStatusCode(),
                'content_length' => strlen($response->getContent()),
            ]);

            // Set performance context
            $performanceData = [
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_bytes' => $endMemory - $startMemory,
                'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ];

            $this->sentryService->setContext('performance', $performanceData);

            // Log performance data to Sentry
            $this->sentryService->logPerformance($performanceData, $request);

            // Finish transaction with success status
            if ($this->transaction) {
                $this->transaction->setHttpStatus($response->getStatusCode());
                $this->transaction->finish();
            }

            return $response;

        } catch (\Throwable $exception) {
            // Report exception to Sentry
            $this->sentryService->reportException($exception, [
                'request' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'route' => $request->route()?->getName(),
                ]
            ], [
                'request_method' => $request->method(),
                'route_name' => $request->route()?->getName() ?? 'unknown',
            ]);

            // Finish transaction with error status
            if ($this->transaction) {
                $this->transaction->setStatus('internal_error');
                $this->transaction->finish();
            }

            throw $exception;
        }
    }

    /**
     * Determine if performance tracking should be enabled
     *
     * @return bool
     */
    protected function shouldTrackPerformance(): bool
    {
        // Enable in production and staging environments
        if (app()->environment(['production', 'staging'])) {
            return true;
        }

        // Enable in local environment if explicitly configured
        return config('logging-package.sentry.enable_local_tracking', false);
    }

    /**
     * Get transaction name for the request
     *
     * @param Request $request
     * @return string
     */
    protected function getTransactionName(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        if ($route && $route->uri()) {
            return "{$request->method()} {$route->uri()}";
        }

        return "{$request->method()} {$request->path()}";
    }

    /**
     * Set user context for Sentry if user is authenticated
     *
     * @param Request $request
     */
    protected function setUserContext(Request $request): void
    {
        if ($request->user()) {
            $this->sentryService->setUser([
                'id' => $request->user()->id,
                'email' => $request->user()->email ?? null,
                'ip_address' => $request->ip(),
            ]);
        }
    }
}
