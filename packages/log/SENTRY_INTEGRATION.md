# Sentry Integration Guide

## Overview

The BoxPos application now includes comprehensive Sentry integration for error monitoring and performance tracking. This integration extends the existing log package to provide enhanced error reporting and performance insights.

## Features

- **Error Tracking**: Automatic exception reporting with context
- **Performance Monitoring**: Request timing and resource usage tracking
- **SQL Query Monitoring**: Slow query and N+1 detection
- **User Context**: Automatic user information inclusion
- **Custom Breadcrumbs**: Detailed activity tracking
- **Environment-aware**: Different behavior per environment

## Configuration

### Environment Variables

Add the following to your `.env` file:

```bash
# Required - Your Sentry DSN
SENTRY_LARAVEL_DSN=your-sentry-dsn-here

# Optional - Performance Monitoring
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0
SENTRY_ENVIRONMENT="${APP_ENV}"

# Optional - Privacy Settings
SENTRY_SEND_DEFAULT_PII=false
SENTRY_CAPTURE_SQL_QUERIES=true

# Optional - Local Development
SENTRY_ENABLE_LOCAL_TRACKING=false

# Optional - Feature Toggles
SENTRY_REPORT_SLOW_QUERIES=true
SENTRY_REPORT_N_PLUS_ONE=true
SENTRY_REPORT_PERFORMANCE_ISSUES=true

# Optional - Thresholds
SENTRY_EXECUTION_THRESHOLD_MS=1000
SENTRY_MEMORY_THRESHOLD_MB=100
```

### Getting Your Sentry DSN

1. Create a project in [Sentry.io](https://sentry.io)
2. Choose Laravel as your platform
3. Copy the DSN from the project settings
4. Add it to your `.env` file

## Usage

### Automatic Features

The following features work automatically once configured:

- **Exception Reporting**: All exceptions are automatically reported
- **Performance Monitoring**: HTTP requests are tracked with timing data
- **SQL Query Monitoring**: Slow queries and N+1 patterns are detected
- **User Context**: Authenticated user information is included

### Manual Reporting

You can manually report events using the SentryService:

#### Using the Service Class

```php
use Packages\Log\Services\SentryService;

class YourController extends Controller
{
    public function __construct(
        private SentryService $sentry
    ) {}

    public function yourMethod()
    {
        // Report an exception
        try {
            // Some risky operation
        } catch (Exception $e) {
            $this->sentry->reportException($e, [
                'context' => 'additional data',
                'user_action' => 'what user was doing'
            ], [
                'feature' => 'payment',
                'severity' => 'high'
            ]);
        }

        // Report a custom message
        $this->sentry->reportMessage(
            'Custom event occurred',
            'info',
            ['details' => 'event details'],
            ['category' => 'business_logic']
        );

        // Add breadcrumbs for tracking
        $this->sentry->addBreadcrumb(
            'User clicked payment button',
            'user_interaction',
            'info',
            ['amount' => 100, 'currency' => 'USD']
        );
    }
}
```

#### Using the Facade

```php
use Packages\Log\Facades\Sentry;

// Report exception
Sentry::reportException($exception, $context, $tags);

// Report message
Sentry::reportMessage('Something happened', 'warning');

// Add breadcrumb
Sentry::addBreadcrumb('User action', 'interaction');

// Set user context
Sentry::setUser([
    'id' => 123,
    'email' => 'user@example.com'
]);

// Set custom tags
Sentry::setTag('feature', 'checkout');

// Set custom context
Sentry::setContext('business_data', [
    'order_id' => 456,
    'total' => 99.99
]);
```

### Performance Monitoring

Performance monitoring is automatically enabled through middleware. You can also manually track performance:

```php
// Start a custom transaction
$transaction = Sentry::startTransaction(
    'custom-operation',
    'background.job',
    ['job_type' => 'data_processing']
);

// Do your work...

// Finish the transaction
$transaction->finish();
```

## Testing

Test your Sentry integration using the built-in command:

```bash
# Test all features
./vendor/bin/sail artisan log:test-sentry

# Test specific feature
./vendor/bin/sail artisan log:test-sentry --type=error
./vendor/bin/sail artisan log:test-sentry --type=message
./vendor/bin/sail artisan log:test-sentry --type=performance
```

## Middleware

The application includes two middleware classes:

### SentryPerformanceMiddleware

Automatically tracks HTTP request performance:
- Request timing
- Memory usage
- Response codes
- User context
- Exception reporting

### Configuration

The middleware is automatically applied to web routes. You can disable it by:

1. Setting `SENTRY_LARAVEL_DSN` to empty in your environment
2. Or setting `SENTRY_ENABLE_LOCAL_TRACKING=false` for local development

## Error Handling

The `SentryExceptionHandler` extends Laravel's default exception handler to:

- Report exceptions to Sentry with enhanced context
- Maintain compatibility with existing logging
- Add severity levels based on exception types
- Include request and user context automatically

## Performance Monitoring Features

### SQL Query Monitoring

Automatically detects and reports:
- Slow queries (configurable threshold)
- N+1 query patterns
- Duplicate queries
- High query counts per request

### Request Monitoring

Tracks:
- Execution time
- Memory usage
- Response sizes
- Route information
- User context

## Environment Behavior

### Production
- All features enabled by default
- Full error reporting
- Performance monitoring active

### Staging
- All features enabled
- Used for testing before production

### Local Development
- Sentry disabled by default
- Can be enabled with `SENTRY_ENABLE_LOCAL_TRACKING=true`
- Useful for testing integration

## Best Practices

### Error Context

Always provide meaningful context when reporting errors:

```php
Sentry::reportException($exception, [
    'user_id' => auth()->id(),
    'request_data' => $request->all(),
    'business_context' => 'checkout_process',
    'step' => 'payment_validation'
]);
```

### Breadcrumbs

Use breadcrumbs to trace user actions leading to errors:

```php
Sentry::addBreadcrumb('User started checkout', 'user_action');
Sentry::addBreadcrumb('Payment method selected', 'user_action');
Sentry::addBreadcrumb('Order validation started', 'business_logic');
// ... error occurs here, breadcrumbs provide context
```

### Tags for Filtering

Use consistent tags for easy filtering in Sentry:

```php
Sentry::setTag('feature', 'checkout');
Sentry::setTag('payment_method', 'credit_card');
Sentry::setTag('user_type', 'premium');
```

### Custom Context

Add business-specific context:

```php
Sentry::setContext('order', [
    'id' => $order->id,
    'total' => $order->total,
    'items_count' => $order->items->count()
]);
```

## Troubleshooting

### Common Issues

1. **Events not appearing in Sentry**
   - Check your DSN is correct
   - Verify network connectivity
   - Check Sentry quotas

2. **Too many events**
   - Adjust sample rates
   - Use ignore rules for common exceptions

3. **Performance data missing**
   - Ensure `SENTRY_TRACES_SAMPLE_RATE` is set
   - Check middleware is properly registered

### Debug Mode

Enable debug logging by adding to your Sentry config:

```php
'logger' => Sentry\Logger\DebugFileLogger::class,
```

This will log Sentry activity to `storage/logs/sentry.log`.

## Integration with Existing Logging

Sentry works alongside the existing log package:

- Exceptions are logged to both local files and Sentry
- Performance data is available in both local logs and Sentry
- SQL query issues are reported to both systems
- No disruption to existing logging functionality

## Security Considerations

- Sensitive data (passwords, tokens) is automatically filtered
- User emails are only sent if explicitly configured
- Request bodies are not sent by default
- SQL bindings can be excluded for security

## Monitoring and Alerts

Configure Sentry alerts for:
- New error types
- Error rate spikes
- Performance degradation
- High memory usage
- Slow database queries

This provides proactive monitoring and quick response to issues.
