<?php

namespace Packages\Log\Traits;

use Packages\Log\Services\LogService;

trait Loggable
{
    /**
     * Log user activity
     */
    protected function logActivity(string $action, array $data = [], ?int $userId = null): void
    {
        $logService = app(LogService::class);
        $logService->logUserActivity($action, $userId ?? auth()->id(), $data);
    }

    /**
     * Log custom event
     */
    protected function logEvent(string $event, array $data = []): void
    {
        $logService = app(LogService::class);
        $logService->logCustomEvent($event, $data);
    }

    /**
     * Log error with context
     */
    protected function logError(\Throwable $exception, array $context = []): void
    {
        $logService = app(LogService::class);
        $logService->logError($exception, $context);
    }

    /**
     * Log info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $logService = app(LogService::class);
        $logService->logCustomEvent($message, $context);
    }

    /**
     * Log with automatic context detection
     */
    protected function logWithContext(string $action, array $data = []): void
    {
        // Add automatic context based on the class using this trait
        $context = [
            'class' => static::class,
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ];

        if (request()) {
            $context['request'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
            ];
        }

        $this->logActivity($action, array_merge($data, ['context' => $context]));
    }

    /**
     * Log model events (create, update, delete)
     */
    protected function logModelEvent(string $event, $model, array $additionalData = []): void
    {
        $modelClass = get_class($model);
        $modelData = [
            'model_class' => $modelClass,
            'model_id' => $model->getKey(),
        ];

        // Add model attributes for create/update events
        if (in_array($event, ['created', 'updated'])) {
            $modelData['attributes'] = $model->getAttributes();
        }

        // Add original attributes for update events
        if ($event === 'updated' && method_exists($model, 'getOriginal')) {
            $modelData['original_attributes'] = $model->getOriginal();
            $modelData['changed_attributes'] = $model->getChanges();
        }

        $this->logActivity("model_{$event}", array_merge($modelData, $additionalData));
    }

    /**
     * Log business process steps
     */
    protected function logProcessStep(string $process, string $step, array $data = []): void
    {
        $this->logActivity("{$process}_{$step}", [
            'process' => $process,
            'step' => $step,
            'data' => $data,
        ]);
    }

    /**
     * Log performance metrics for a specific operation
     */
    protected function logOperationPerformance(string $operation, float $startTime, array $additionalData = []): void
    {
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->logEvent('operation_performance', [
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ] + $additionalData);
    }

    /**
     * Helper method to measure and log operation performance
     */
    protected function withPerformanceLogging(string $operation, callable $callback, array $additionalData = [])
    {
        $startTime = microtime(true);
        
        try {
            $result = $callback();
            $this->logOperationPerformance($operation, $startTime, $additionalData);
            return $result;
        } catch (\Throwable $e) {
            $this->logOperationPerformance($operation, $startTime, array_merge($additionalData, [
                'error' => true,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]));
            throw $e;
        }
    }
}
