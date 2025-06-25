<?php

namespace Packages\Log\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Packages\Log\Services\SentryService;
use Packages\Log\Services\LogService;
use Sentry\Laravel\Integration;

/**
 * Custom exception handler that integrates Sentry with existing logging
 * 
 * This handler extends Laravel's default exception handler to provide
 * enhanced error reporting with Sentry while maintaining compatibility
 * with the existing log package.
 */
class SentryExceptionHandler extends ExceptionHandler
{
    protected ?SentryService $sentryService = null;
    protected ?LogService $logService = null;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Initialize services
     */
    public function __construct()
    {
        parent::__construct(app());
        
        if (config('logging-package.sentry.enabled', false)) {
            $this->sentryService = app(SentryService::class);
        }
        
        $this->logService = app(LogService::class);
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        // Use existing log service for standard logging
        $this->logService->logError($exception, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Report to Sentry if enabled and not in dontReport list
        if ($this->sentryService && $this->shouldReport($exception)) {
            $this->reportToSentry($exception);
        }

        parent::report($exception);
    }

    /**
     * Report exception to Sentry with enhanced context
     *
     * @param Throwable $exception
     */
    protected function reportToSentry(Throwable $exception): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
        ];

        // Add request context if available
        if (request()) {
            $context['request'] = [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'route' => request()->route()?->getName(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // Add user context if authenticated
            if (auth()->check()) {
                $context['user'] = [
                    'id' => auth()->id(),
                    'email' => auth()->user()->email ?? null,
                ];
            }
        }

        $tags = [
            'environment' => app()->environment(),
            'exception_type' => get_class($exception),
            'severity' => $this->getExceptionSeverity($exception),
        ];

        $this->sentryService->reportException($exception, $context, $tags);
    }

    /**
     * Determine the severity of an exception
     *
     * @param Throwable $exception
     * @return string
     */
    protected function getExceptionSeverity(Throwable $exception): string
    {
        // Critical exceptions that should be addressed immediately
        $criticalExceptions = [
            \ErrorException::class,
            \Error::class,
            \ParseError::class,
            \TypeError::class,
        ];

        // Warning level exceptions
        $warningExceptions = [
            \InvalidArgumentException::class,
            \RuntimeException::class,
        ];

        $exceptionClass = get_class($exception);

        if (in_array($exceptionClass, $criticalExceptions)) {
            return 'fatal';
        }

        if (in_array($exceptionClass, $warningExceptions)) {
            return 'warning';
        }

        // Check for HTTP exceptions
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
            
            if ($statusCode >= 500) {
                return 'error';
            } elseif ($statusCode >= 400) {
                return 'warning';
            }
        }

        return 'error';
    }

    /**
     * Determine if the exception should be reported to Sentry
     *
     * @param Throwable $exception
     * @return bool
     */
    protected function shouldReportToSentry(Throwable $exception): bool
    {
        // Don't report if Sentry is not enabled
        if (!$this->sentryService) {
            return false;
        }

        // Don't report in local environment unless explicitly enabled
        if (app()->environment('local') && !config('logging-package.sentry.enable_local_tracking', false)) {
            return false;
        }

        // Use Laravel's built-in shouldReport method
        return $this->shouldReport($exception);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Additional custom reporting logic can be added here
            if ($this->sentryService) {
                $this->sentryService->addBreadcrumb(
                    message: "Exception occurred: " . get_class($e),
                    category: 'exception',
                    level: 'error',
                    data: [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]
                );
            }
        });
    }
}
