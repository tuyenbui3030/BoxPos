<?php

namespace Packages\Log\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Packages\Log\Services\LogFormatterService;
use Packages\Log\Services\QueryPerformanceService;
use Packages\Log\Exceptions\LoggingException;

class LogService
{
    protected LogFormatterService $formatter;
    protected QueryPerformanceService $queryPerformance;
    protected ?string $requestId = null;

    public function __construct(
        LogFormatterService $formatter,
        QueryPerformanceService $queryPerformance
    ) {
        $this->formatter = $formatter;
        $this->queryPerformance = $queryPerformance;
        $this->requestId = $this->generateRequestId();
    }

    /**
     * Log user activity to laravel.log
     */
    public function logUserActivity(string $action, ?int $userId = null, array $data = []): void
    {
        if (!config('logging-package.user_activity.enabled', true)) {
            return;
        }

        $context = $this->buildUserActivityContext($action, $userId, $data);
        
        Log::channel('daily')->info('User Activity', $context);
    }

    /**
     * Log API request to laravel.log
     */
    public function logApiRequest($request, $response, float $duration): void
    {
        if (!config('logging-package.requests.enabled', true)) {
            return;
        }

        $context = $this->buildApiRequestContext($request, $response, $duration);
        
        Log::channel('daily')->info('API Request', $context);
    }

    /**
     * Log error to error.log
     */
    public function logError(\Throwable $exception, array $context = []): void
    {
        $errorContext = $this->buildErrorContext($exception, $context);
        
        Log::channel('error')->error($exception->getMessage(), $errorContext);
        
        // Send to Slack if critical and enabled
        if ($this->isCriticalError($exception) && config('logging-package.slack.enabled', false)) {
            $this->sendToSlack($exception, $errorContext);
        }
    }

    /**
     * Log custom event to laravel.log
     */
    public function logCustomEvent(string $event, array $data = []): void
    {
        $context = $this->buildCustomEventContext($event, $data);
        
        Log::channel('daily')->info('Custom Event', $context);
    }

    /**
     * Log SQL query to sql.log
     */
    public function logSqlQuery(string $query, float $time, array $bindings = []): void
    {
        if (!config('logging-package.sql.enabled', false)) {
            return;
        }

        $context = $this->buildSqlQueryContext($query, $time, $bindings);
        
        // Log as warning if slow query
        $threshold = config('logging-package.sql.threshold_ms', 500);
        $level = $time > $threshold ? 'warning' : 'debug';
        
        if ($level === 'warning') {
            $context['slow_query'] = true;
            $context['threshold_ms'] = $threshold;
            Log::channel('sql')->warning('Slow Query Detected', $context);
        } else {
            Log::channel('sql')->debug('SQL Query', $context);
        }

        // Track for performance analysis
        $this->queryPerformance->trackQuery($query, $time, $bindings);
    }

    /**
     * Start query tracking for a request
     */
    public function startQueryTracking(): void
    {
        $this->queryPerformance->startRequestTracking();
    }

    /**
     * Stop query tracking and analyze
     */
    public function stopQueryTracking(): array
    {
        return $this->queryPerformance->stopRequestTracking();
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(array $metrics): void
    {
        if (!config('logging-package.performance.enabled', true)) {
            return;
        }

        $context = $this->buildPerformanceContext($metrics);
        
        // Determine log level based on thresholds
        $level = $this->determinePerformanceLogLevel($metrics);
        
        Log::channel('performance')->{$level}('Performance Metrics', $context);
    }

    /**
     * Build user activity context
     */
    protected function buildUserActivityContext(string $action, ?int $userId, array $data): array
    {
        $context = [
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'data' => $this->sanitizeData($data),
            'request_id' => $this->requestId,
            'timestamp' => now()->toISOString(),
        ];

        if (config('logging-package.user_activity.include_ip', true)) {
            $context['ip'] = request()->ip();
        }

        if (config('logging-package.user_activity.include_user_agent', true)) {
            $context['user_agent'] = request()->userAgent();
        }

        return $context;
    }

    /**
     * Build API request context
     */
    protected function buildApiRequestContext($request, $response, float $duration): array
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'duration_ms' => round($duration, 2),
            'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'response_status' => $response->getStatusCode(),
            'request_id' => $this->requestId,
            'timestamp' => now()->toISOString(),
        ];

        if (config('logging-package.requests.include_headers', false)) {
            $context['headers'] = $this->sanitizeHeaders($request->headers->all());
        }

        if (config('logging-package.requests.include_body', false)) {
            $context['request_body'] = $this->sanitizeData($request->all());
        }

        return $context;
    }

    /**
     * Build error context
     */
    protected function buildErrorContext(\Throwable $exception, array $context): array
    {
        $errorContext = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'request_id' => $this->requestId,
            'timestamp' => now()->toISOString(),
            'context' => $this->sanitizeData($context),
        ];

        if (config('logging-package.formatters.include_trace', false)) {
            $errorContext['trace'] = $this->formatStackTrace($exception);
        }

        if (request()) {
            $errorContext['request'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        if (auth()->check()) {
            $errorContext['user_id'] = auth()->id();
        }

        return $errorContext;
    }

    /**
     * Build custom event context
     */
    protected function buildCustomEventContext(string $event, array $data): array
    {
        return [
            'event' => $event,
            'data' => $this->sanitizeData($data),
            'request_id' => $this->requestId,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Build SQL query context
     */
    protected function buildSqlQueryContext(string $query, float $time, array $bindings): array
    {
        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'time_ms' => round($time, 2),
            'request_id' => $this->requestId,
            'timestamp' => now()->toISOString(),
        ];

        // Replace placeholders if enabled
        if (config('logging-package.channels.sql.replace_placeholders', true)) {
            $context['formatted_query'] = $this->formatSqlQuery($query, $bindings);
        }

        return $context;
    }

    /**
     * Build performance context
     */
    protected function buildPerformanceContext(array $metrics): array
    {
        return array_merge($metrics, [
            'request_id' => $this->requestId,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Sanitize sensitive data from logs
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveFields = config('logging-package.requests.sensitive_fields', []);
        
        return collect($data)->mapWithKeys(function ($value, $key) use ($sensitiveFields) {
            if (in_array(strtolower($key), array_map('strtolower', $sensitiveFields))) {
                return [$key => '[REDACTED]'];
            }
            
            if (is_array($value)) {
                return [$key => $this->sanitizeData($value)];
            }
            
            return [$key => $value];
        })->toArray();
    }

    /**
     * Sanitize request headers
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key'];
        
        return collect($headers)->mapWithKeys(function ($value, $key) use ($sensitiveHeaders) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                return [$key => '[REDACTED]'];
            }
            return [$key => $value];
        })->toArray();
    }

    /**
     * Format SQL query with bindings
     */
    protected function formatSqlQuery(string $query, array $bindings): string
    {
        if (empty($bindings)) {
            return $query;
        }

        return str_replace(
            array_fill(0, count($bindings), '?'),
            array_map(function ($binding) {
                return is_string($binding) ? "'{$binding}'" : $binding;
            }, $bindings),
            $query
        );
    }

    /**
     * Format exception stack trace
     */
    protected function formatStackTrace(\Throwable $exception): array
    {
        $maxLines = config('logging-package.formatters.max_trace_lines', 10);
        $trace = $exception->getTrace();
        
        return array_slice($trace, 0, $maxLines);
    }

    /**
     * Generate unique request ID
     */
    protected function generateRequestId(): string
    {
        return 'req_' . Str::random(10);
    }

    /**
     * Determine if error is critical
     */
    protected function isCriticalError(\Throwable $exception): bool
    {
        $criticalExceptions = [
            \Illuminate\Database\QueryException::class,
            \Illuminate\Http\Exceptions\HttpResponseException::class,
        ];

        return in_array(get_class($exception), $criticalExceptions) ||
               $exception->getCode() >= 500;
    }

    /**
     * Send error to Slack
     */
    protected function sendToSlack(\Throwable $exception, array $context): void
    {
        try {
            Log::channel('slack_critical')->critical($exception->getMessage(), $context);
        } catch (\Exception $e) {
            // Fail silently to avoid recursion
            Log::channel('error')->error('Failed to send to Slack: ' . $e->getMessage());
        }
    }

    /**
     * Determine performance log level based on metrics
     */
    protected function determinePerformanceLogLevel(array $metrics): string
    {
        $memoryThreshold = config('logging-package.performance.memory_threshold_mb', 100);
        $executionThreshold = config('logging-package.performance.execution_threshold_ms', 1000);

        $memoryMb = $metrics['memory_mb'] ?? 0;
        $executionMs = $metrics['execution_time_ms'] ?? 0;

        if ($memoryMb > $memoryThreshold || $executionMs > $executionThreshold) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get current request ID
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
