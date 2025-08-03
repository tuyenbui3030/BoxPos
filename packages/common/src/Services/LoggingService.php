<?php

namespace Packages\Common\Services;

use Illuminate\Support\Facades\Log;
use Packages\Common\Models\ActivityLog;
use Packages\Common\Models\ErrorLog;
use Packages\Common\Models\PerformanceLog;
use Packages\Common\Models\SecurityLog;
use Throwable;

class LoggingService
{
    /**
     * Log user activity
     */
    public function logActivity(string $activity, array $context = []): void
    {
        try {
            // Store in database
            ActivityLog::create([
                'activity' => $activity,
                'context' => $context,
                'user_id' => $context['user_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'created_at' => $context['timestamp'] ?? now(),
            ]);

            // Also log to Laravel log for immediate visibility
            Log::info("Activity: {$activity}", $context);
        } catch (Throwable $e) {
            // Fallback to file logging if database fails
            Log::error('Failed to log activity to database', [
                'activity' => $activity,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log errors with full context
     */
    public function logError(Throwable $exception, array $context = []): void
    {
        try {
            // Store in database
            ErrorLog::create([
                'error_type' => get_class($exception),
                'error_message' => $exception->getMessage(),
                'error_code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
                'context' => $context,
                'user_id' => $context['user_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'created_at' => $context['timestamp'] ?? now(),
            ]);

            // Also log to Laravel log
            Log::error($exception->getMessage(), array_merge($context, [
                'exception' => $exception,
            ]));
        } catch (Throwable $e) {
            // Fallback to file logging if database fails
            Log::critical('Failed to log error to database', [
                'original_exception' => $exception->getMessage(),
                'logging_error' => $e->getMessage(),
                'context' => $context,
            ]);
        }
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $operation, float $duration, array $context = []): void
    {
        try {
            // Store in database
            PerformanceLog::create([
                'operation' => $operation,
                'duration' => $duration,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true),
                'context' => $context,
                'user_id' => $context['user_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'created_at' => $context['timestamp'] ?? now(),
            ]);

            // Log to Laravel log if duration is concerning
            if ($duration > 1.0) { // Log slow operations (>1 second)
                Log::warning("Slow operation detected: {$operation}", [
                    'duration' => $duration,
                    'context' => $context,
                ]);
            }
        } catch (Throwable $e) {
            // Fallback to file logging if database fails
            Log::error('Failed to log performance to database', [
                'operation' => $operation,
                'duration' => $duration,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log security events
     */
    public function logSecurity(string $event, array $context = []): void
    {
        try {
            // Store in database
            SecurityLog::create([
                'event' => $event,
                'context' => $context,
                'user_id' => $context['user_id'] ?? null,
                'store_id' => $context['store_id'] ?? null,
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'created_at' => $context['timestamp'] ?? now(),
            ]);

            // Always log security events to Laravel log
            Log::warning("Security Event: {$event}", $context);
        } catch (Throwable $e) {
            // Fallback to file logging if database fails
            Log::critical('Failed to log security event to database', [
                'event' => $event,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log database queries for debugging
     */
    public function logDatabaseQuery(string $query, array $bindings = [], ?float $time = null, array $context = []): void
    {
        if (config('app.debug')) {
            Log::debug('Database Query', [
                'query' => $query,
                'bindings' => $bindings,
                'time' => $time,
                'context' => $context,
            ]);
        }
    }

    /**
     * Log business events
     */
    public function logBusinessEvent(string $event, array $data = []): void
    {
        $this->logActivity("business_event_{$event}", $data);
    }

    /**
     * Get activity logs for a user
     */
    public function getUserActivityLogs(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get error logs for a store
     */
    public function getStoreErrorLogs(int $storeId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ErrorLog::where('store_id', $storeId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get performance metrics for analysis
     */
    public function getPerformanceMetrics(string $operation = null, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        $query = PerformanceLog::where('created_at', '>=', now()->subHours($hours));
        
        if ($operation) {
            $query->where('operation', $operation);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Clean old logs based on retention policy
     */
    public function cleanOldLogs(): void
    {
        $retentionDays = config('logging.retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        try {
            ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            ErrorLog::where('created_at', '<', $cutoffDate)->delete();
            PerformanceLog::where('created_at', '<', $cutoffDate)->delete();
            SecurityLog::where('created_at', '<', $cutoffDate)->delete();

            Log::info('Old logs cleaned successfully', [
                'cutoff_date' => $cutoffDate,
                'retention_days' => $retentionDays,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to clean old logs', [
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate,
            ]);
        }
    }
}