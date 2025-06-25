<?php

namespace Packages\Log\Facades;

use Illuminate\Support\Facades\Facade;
use Packages\Log\Services\SentryService;

/**
 * Sentry Facade for easy access to SentryService
 * 
 * @method static string|null reportException(\Throwable $exception, array $context = [], array $tags = [])
 * @method static string|null reportMessage(string $message, string $level = 'info', array $context = [], array $tags = [])
 * @method static \Sentry\Tracing\Transaction|null startTransaction(string $name, string $operation = 'http.request', array $data = [])
 * @method static void addBreadcrumb(string $message, string $category = 'default', string $level = 'info', array $data = [])
 * @method static void setUser(array $user)
 * @method static void setTag(string $key, string $value)
 * @method static void setContext(string $key, array $context)
 * @method static void logPerformance(array $performanceData, \Illuminate\Http\Request|null $request = null)
 * @method static void logSlowQuery(array $queryData)
 * @method static void logNPlusOneQuery(array $queryData)
 */
class Sentry extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return SentryService::class;
    }
}
