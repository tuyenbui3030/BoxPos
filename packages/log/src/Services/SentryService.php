<?php

namespace Packages\Log\Services;

use Sentry\Laravel\Facade as Sentry;
use Sentry\State\Scope;
use Sentry\Severity;
use Sentry\Tracing\TransactionContext;
use Sentry\Breadcrumb;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * Service for handling Sentry error reporting and performance monitoring
 * 
 * This service provides a unified interface for sending errors and performance
 * data to Sentry while following the project's logging standards.
 */
class SentryService
{
    /**
     * Report an exception to Sentry with context
     *
     * @param Throwable $exception
     * @param array $context Additional context data
     * @param array $tags Tags to add to the event
     * @return string|null The event ID
     */
    public function reportException(Throwable $exception, array $context = [], array $tags = []): ?string
    {
        return Sentry::withScope(function (Scope $scope) use ($exception, $context, $tags) {
            // Add user context if authenticated
            if (Auth::check()) {
                $scope->setUser([
                    'id' => Auth::id(),
                    'email' => Auth::user()->email ?? null,
                ]);
            }

            // Add custom context
            if (!empty($context)) {
                $scope->setContext('custom', $context);
            }

            // Add tags
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            return Sentry::captureException($exception);
        });
    }

    /**
     * Report a message to Sentry
     *
     * @param string $message
     * @param string $level
     * @param array $context
     * @param array $tags
     * @return string|null
     */
    public function reportMessage(string $message, string $level = 'info', array $context = [], array $tags = []): ?string
    {
        return Sentry::withScope(function (Scope $scope) use ($message, $level, $context, $tags) {
            // Add user context if authenticated
            if (Auth::check()) {
                $scope->setUser([
                    'id' => Auth::id(),
                    'email' => Auth::user()->email ?? null,
                ]);
            }

            // Add custom context
            if (!empty($context)) {
                $scope->setContext('custom', $context);
            }

            // Add tags
            foreach ($tags as $key => $value) {
                $scope->setTag($key, $value);
            }

            // Convert string level to Sentry Severity
            $sentryLevel = $this->convertLevelToSeverity($level);
            $scope->setLevel($sentryLevel);

            return Sentry::captureMessage($message);
        });
    }

    /**
     * Start a performance transaction
     *
     * @param string $name Transaction name
     * @param string $operation Operation type
     * @param array $data Additional data
     * @return \Sentry\Tracing\Transaction|null
     */
    public function startTransaction(string $name, string $operation = 'http.request', array $data = [])
    {
        $context = new TransactionContext($name, $operation);
        
        if (!empty($data)) {
            $context->setData($data);
        }

        return Sentry::startTransaction($context);
    }

    /**
     * Add breadcrumb for tracking user actions
     *
     * @param string $message
     * @param string $category
     * @param string $level
     * @param array $data
     */
    public function addBreadcrumb(string $message, string $category = 'default', string $level = 'info', array $data = []): void
    {
        $breadcrumb = new Breadcrumb(
            $this->convertLevelToSeverity($level),
            Breadcrumb::TYPE_DEFAULT,
            $category,
            $message,
            $data
        );
        
        Sentry::addBreadcrumb($breadcrumb);
    }

    /**
     * Set user context for Sentry
     *
     * @param array $user
     */
    public function setUser(array $user): void
    {
        Sentry::configureScope(function (Scope $scope) use ($user) {
            $scope->setUser($user);
        });
    }

    /**
     * Set tag for current scope
     *
     * @param string $key
     * @param string $value
     */
    public function setTag(string $key, string $value): void
    {
        Sentry::configureScope(function (Scope $scope) use ($key, $value) {
            $scope->setTag($key, $value);
        });
    }

    /**
     * Set context for current scope
     *
     * @param string $key
     * @param array $context
     */
    public function setContext(string $key, array $context): void
    {
        Sentry::configureScope(function (Scope $scope) use ($key, $context) {
            $scope->setContext($key, $context);
        });
    }

    /**
     * Log performance data to Sentry
     *
     * @param array $performanceData
     * @param Request|null $request
     */
    public function logPerformance(array $performanceData, ?Request $request = null): void
    {
        $this->addBreadcrumb(
            message: 'Performance metrics recorded',
            category: 'performance',
            level: 'info',
            data: $performanceData
        );

        // Set performance context
        $this->setContext('performance', $performanceData);

        // Add request context if provided
        if ($request) {
            $this->setContext('request', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);
        }

        // Report as message if thresholds are exceeded
        if ($this->shouldReportPerformanceIssue($performanceData)) {
            $this->reportMessage(
                'Performance threshold exceeded',
                'warning',
                $performanceData,
                ['performance_issue' => 'true']
            );
        }
    }

    /**
     * Check if performance data indicates an issue that should be reported
     *
     * @param array $performanceData
     * @return bool
     */
    protected function shouldReportPerformanceIssue(array $performanceData): bool
    {
        $executionThreshold = config('logging-package.performance.execution_threshold_ms', 1000);
        $memoryThreshold = config('logging-package.performance.memory_threshold_mb', 100);

        return ($performanceData['execution_time_ms'] ?? 0) > $executionThreshold ||
               ($performanceData['memory_used_mb'] ?? 0) > $memoryThreshold;
    }

    /**
     * Convert string level to Sentry Severity
     *
     * @param string $level
     * @return Severity
     */
    protected function convertLevelToSeverity(string $level): Severity
    {
        return match (strtolower($level)) {
            'debug' => Severity::debug(),
            'info' => Severity::info(),
            'warning', 'warn' => Severity::warning(),
            'error' => Severity::error(),
            'fatal', 'critical' => Severity::fatal(),
            default => Severity::info(),
        };
    }

    /**
     * Log SQL query performance issues to Sentry
     *
     * @param array $queryData
     */
    public function logSlowQuery(array $queryData): void
    {
        $this->addBreadcrumb(
            message: 'Slow query detected',
            category: 'database',
            level: 'warning',
            data: $queryData
        );

        $this->reportMessage(
            'Slow SQL query detected',
            'warning',
            $queryData,
            ['slow_query' => 'true', 'database' => 'true']
        );
    }

    /**
     * Log N+1 query detection to Sentry
     *
     * @param array $queryData
     */
    public function logNPlusOneQuery(array $queryData): void
    {
        $this->addBreadcrumb(
            message: 'Potential N+1 query detected',
            category: 'database',
            level: 'warning',
            data: $queryData
        );

        $this->reportMessage(
            'Potential N+1 query pattern detected',
            'error',
            $queryData,
            ['n_plus_one' => 'true', 'database' => 'true']
        );
    }
}
