<?php

namespace Packages\Common\Traits;

use Illuminate\Support\Facades\Log;
use Packages\Common\Services\LoggingService;
use Throwable;

trait Loggable
{
    /**
     * Log an activity with context
     */
    protected function logActivity(string $activity, array $context = []): void
    {
        $loggingService = app(LoggingService::class);
        
        $loggingService->logActivity($activity, array_merge($context, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]));
    }

    /**
     * Log an error with context
     */
    protected function logError(Throwable $exception, array $context = []): void
    {
        $loggingService = app(LoggingService::class);
        
        $loggingService->logError($exception, array_merge($context, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]));
    }

    /**
     * Log performance metrics
     */
    protected function logPerformanceMetric(string $operation, float $duration, array $context = []): void
    {
        $loggingService = app(LoggingService::class);
        
        $loggingService->logPerformance($operation, $duration, array_merge($context, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'timestamp' => now(),
        ]));
    }

    /**
     * Log security events
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        $loggingService = app(LoggingService::class);
        
        $loggingService->logSecurity($event, array_merge($context, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]));
    }

    /**
     * Log database queries for debugging
     */
    protected function logDatabaseQuery(string $query, array $bindings = [], float $time = null): void
    {
        if (config('app.debug')) {
            $loggingService = app(LoggingService::class);
            
            $loggingService->logDatabaseQuery($query, $bindings, $time, [
                'class' => static::class,
                'user_id' => auth()->id(),
                'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
                'timestamp' => now(),
            ]);
        }
    }

    /**
     * Log business events
     */
    protected function logBusinessEvent(string $event, array $data = []): void
    {
        $loggingService = app(LoggingService::class);
        
        $loggingService->logBusinessEvent($event, array_merge($data, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'timestamp' => now(),
        ]));
    }

    /**
     * Start performance tracking
     */
    protected function startPerformanceTracking(): float
    {
        return microtime(true);
    }

    /**
     * End performance tracking and log
     */
    protected function endPerformanceTracking(float $startTime, string $operation, array $context = []): void
    {
        $duration = microtime(true) - $startTime;
        $this->logPerformanceMetric($operation, $duration, $context);
    }

    /**
     * Log with automatic context enrichment
     */
    protected function logWithContext(string $level, string $message, array $context = []): void
    {
        $enrichedContext = array_merge($context, [
            'class' => static::class,
            'user_id' => auth()->id(),
            'store_id' => auth()->check() ? auth()->user()->current_store_id : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ]);

        Log::log($level, $message, $enrichedContext);
    }
}