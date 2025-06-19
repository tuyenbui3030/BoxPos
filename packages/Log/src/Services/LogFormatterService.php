<?php

namespace Packages\Log\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LogFormatterService
{
    /**
     * Format log entry as JSON
     */
    public function formatAsJson(array $data, bool $prettyPrint = false): string
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        
        if ($prettyPrint || config('logging-package.formatters.json_pretty_print', false)) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $flags);
    }

    /**
     * Format log entry as structured text
     */
    public function formatAsStructured(array $data): string
    {
        $lines = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $lines[] = $key . ': ' . json_encode($value);
            } else {
                $lines[] = $key . ': ' . $value;
            }
        }

        return implode(' | ', $lines);
    }

    /**
     * Format log entry as single line
     */
    public function formatAsLine(array $data): string
    {
        $message = $data['message'] ?? 'Log Entry';
        unset($data['message']);

        $context = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $context[] = "{$key}={$value}";
            }
        }

        return $message . ' [' . implode(', ', $context) . ']';
    }

    /**
     * Format SQL query for logging
     */
    public function formatSqlQuery(string $query, array $bindings = [], float $time = 0): array
    {
        $formatted = [
            'query' => $this->cleanSqlQuery($query),
            'bindings' => $bindings,
            'execution_time_ms' => round($time, 2),
            'query_type' => $this->determineSqlQueryType($query),
            'table_name' => $this->extractTableName($query),
        ];

        if (config('logging-package.channels.sql.replace_placeholders', true)) {
            $formatted['formatted_query'] = $this->replacePlaceholders($query, $bindings);
        }

        return $formatted;
    }

    /**
     * Format performance metrics
     */
    public function formatPerformanceMetrics(array $metrics): array
    {
        return [
            'execution_time_ms' => round($metrics['execution_time'] ?? 0, 2),
            'memory_usage_mb' => round(($metrics['memory_usage'] ?? 0) / 1024 / 1024, 2),
            'memory_peak_mb' => round(($metrics['memory_peak'] ?? 0) / 1024 / 1024, 2),
            'cpu_usage_percent' => round($metrics['cpu_usage'] ?? 0, 2),
            'query_count' => $metrics['query_count'] ?? 0,
            'slow_query_count' => $metrics['slow_query_count'] ?? 0,
        ];
    }

    /**
     * Format user activity for logging
     */
    public function formatUserActivity(string $action, int $userId, array $data = []): array
    {
        return [
            'action' => $action,
            'user_id' => $userId,
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => $this->formatUserAgent(request()->userAgent()),
            'session_id' => session()->getId(),
            'timestamp' => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Format API request for logging
     */
    public function formatApiRequest($request, $response = null, float $duration = 0): array
    {
        $formatted = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => optional($request->route())->getName(),
            'duration_ms' => round($duration, 2),
            'ip_address' => $request->ip(),
            'user_agent' => $this->formatUserAgent($request->userAgent()),
        ];

        if ($response) {
            $formatted['response_status'] = $response->getStatusCode();
            $formatted['response_size_bytes'] = strlen($response->getContent());
        }

        if (auth()->check()) {
            $formatted['user_id'] = auth()->id();
        }

        return $formatted;
    }

    /**
     * Format error for logging
     */
    public function formatError(\Throwable $exception, array $context = []): array
    {
        $formatted = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'context' => $context,
        ];

        if (config('logging-package.formatters.include_trace', false)) {
            $formatted['stack_trace'] = $this->formatStackTrace($exception);
        }

        if (request()) {
            $formatted['request_info'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];
        }

        return $formatted;
    }

    /**
     * Format stack trace for readability
     */
    public function formatStackTrace(\Throwable $exception): array
    {
        $trace = $exception->getTrace();
        $maxLines = config('logging-package.formatters.max_trace_lines', 10);
        
        return array_slice(array_map(function ($item) {
            return [
                'file' => $item['file'] ?? 'unknown',
                'line' => $item['line'] ?? 0,
                'function' => $item['function'] ?? 'unknown',
                'class' => $item['class'] ?? null,
            ];
        }, $trace), 0, $maxLines);
    }

    /**
     * Clean SQL query for logging
     */
    protected function cleanSqlQuery(string $query): string
    {
        // Remove extra whitespace
        $query = preg_replace('/\s+/', ' ', trim($query));
        
        // Remove comments
        $query = preg_replace('/--.*$/m', '', $query);
        $query = preg_replace('/\/\*.*?\*\//s', '', $query);
        
        return trim($query);
    }

    /**
     * Determine SQL query type
     */
    protected function determineSqlQueryType(string $query): string
    {
        $query = strtoupper(trim($query));
        
        if (Str::startsWith($query, 'SELECT')) return 'SELECT';
        if (Str::startsWith($query, 'INSERT')) return 'INSERT';
        if (Str::startsWith($query, 'UPDATE')) return 'UPDATE';
        if (Str::startsWith($query, 'DELETE')) return 'DELETE';
        if (Str::startsWith($query, 'CREATE')) return 'CREATE';
        if (Str::startsWith($query, 'ALTER')) return 'ALTER';
        if (Str::startsWith($query, 'DROP')) return 'DROP';
        
        return 'OTHER';
    }

    /**
     * Extract table name from SQL query
     */
    protected function extractTableName(string $query): ?string
    {
        $query = strtolower(trim($query));
        
        // Handle different query types
        if (preg_match('/^select.*?from\s+`?(\w+)`?/i', $query, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/^(insert\s+into|update|delete\s+from)\s+`?(\w+)`?/i', $query, $matches)) {
            return $matches[2];
        }
        
        return null;
    }

    /**
     * Replace SQL placeholders with actual values
     */
    protected function replacePlaceholders(string $query, array $bindings): string
    {
        if (empty($bindings)) {
            return $query;
        }

        $bindings = array_map(function ($binding) {
            if (is_null($binding)) {
                return 'NULL';
            }
            
            if (is_bool($binding)) {
                return $binding ? 'TRUE' : 'FALSE';
            }
            
            if (is_string($binding)) {
                return "'" . addslashes($binding) . "'";
            }
            
            return $binding;
        }, $bindings);

        return str_replace(
            array_fill(0, count($bindings), '?'),
            $bindings,
            $query
        );
    }

    /**
     * Format user agent for logging
     */
    protected function formatUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return ['raw' => null, 'browser' => null, 'platform' => null];
        }

        return [
            'raw' => $userAgent,
            'browser' => $this->extractBrowser($userAgent),
            'platform' => $this->extractPlatform($userAgent),
        ];
    }

    /**
     * Extract browser from user agent
     */
    protected function extractBrowser(string $userAgent): ?string
    {
        $browsers = [
            'Chrome' => '/Chrome\/[\d.]+/',
            'Firefox' => '/Firefox\/[\d.]+/',
            'Safari' => '/Safari\/[\d.]+/',
            'Edge' => '/Edge\/[\d.]+/',
            'Opera' => '/Opera\/[\d.]+/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return 'Unknown';
    }

    /**
     * Extract platform from user agent
     */
    protected function extractPlatform(string $userAgent): ?string
    {
        $platforms = [
            'Windows' => '/Windows/',
            'macOS' => '/Mac OS X/',
            'Linux' => '/Linux/',
            'Android' => '/Android/',
            'iOS' => '/iPhone|iPad/',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $platform;
            }
        }

        return 'Unknown';
    }

    /**
     * Format log entry based on configured format
     */
    public function format(array $data, string $format = null): string
    {
        $format = $format ?: config('logging-package.formatters.default', 'line');

        return match ($format) {
            'json' => $this->formatAsJson($data),
            'structured' => $this->formatAsStructured($data),
            'line' => $this->formatAsLine($data),
            default => $this->formatAsLine($data),
        };
    }
}
