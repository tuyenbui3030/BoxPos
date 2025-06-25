# Log Package

A comprehensive logging package for Laravel applications optimized for local/VPS deployment with support for multiple log channels, SQL query tracking, performance monitoring, and Sentry integration.

## Features

- **Separate Log Files**: Stores logs in separate files (laravel.log, error.log, sql.log)
- **SQL Query Logging**: Track and analyze database queries with N+1 detection
- **Performance Monitoring**: Monitor memory usage and execution time
- **Sentry Integration**: Error tracking and performance monitoring with Sentry
- **Log Rotation & Compression**: Automatic cleanup and compression of old logs
- **Custom Channels**: Support for remote file storage and Slack notifications
- **Request Tracking**: Log HTTP requests and responses with duration
- **CloudWatch Ready**: Structured for future CloudWatch integration

## Installation

1. Add the package to your main application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/Log"
        }
    ],
    "require": {
        "packages/log": "*"
    }
}
```

2. Install via Composer:
```bash
./vendor/bin/sail composer install
```

3. The service provider will be auto-discovered. If not, add it manually to `config/app.php`:
```php
'providers' => [
    // Other providers...
    Packages\Log\LogServiceProvider::class,
],
```

4. Publish the configuration file:
```bash
./vendor/bin/sail artisan vendor:publish --tag=log-config
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Basic Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_SQL=true
LOG_SLOW_QUERIES=true
SLOW_QUERY_THRESHOLD=500
LOG_REQUESTS=true
LOG_PERFORMANCE=true

# Sentry Integration
SENTRY_LARAVEL_DSN=your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0
SENTRY_ENVIRONMENT="${APP_ENV}"
SENTRY_SEND_DEFAULT_PII=false
SENTRY_CAPTURE_SQL_QUERIES=true

# Storage Management
LOG_RETENTION_DAYS=14
LOG_COMPRESS=true
LOG_MAX_SIZE=100M

# Remote Storage (optional)
LOG_TO_REMOTE_FILE=false
REMOTE_LOG_PATH=/var/log/shared/laravel-logs
REMOTE_LOG_MOUNT_POINT=/mnt/logs

# Slack Notifications (optional)
LOG_TO_SLACK=false
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### Log Channels

The package creates three main log files:

- **laravel.log**: General application logs (info, warning, debug)
- **error.log**: Error and critical logs
- **sql.log**: Database query logs and performance analysis

## Usage

### 1. Using LogService Directly

```php
use Packages\Log\Services\LogService;

class CustomerController extends Controller
{
    protected LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        
        $this->logService->logUserActivity('customer_created', auth()->id(), [
            'customer_id' => $customer->id,
            'email' => $customer->email
        ]);
        
        return response()->json($customer, 201);
    }
}
```

### 2. Using Loggable Trait

```php
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        
        $this->logActivity('customer_created', [
            'customer_id' => $customer->id,
            'email' => $customer->email
        ]);
        
        return response()->json($customer, 201);
    }
}
```

### 3. Using LogsQueries Trait

```php
use Packages\Log\Traits\LogsQueries;

class CustomerService
{
    use LogsQueries;

    public function createCustomer(array $data): Customer
    {
        return $this->withQueryLogging('customer_creation', function() use ($data) {
            return Customer::create($data);
        });
    }
}
```

### 4. Middleware Usage

Add middleware to your routes or controllers:

```php
// In routes/web.php
Route::middleware(['log.requests', 'log.performance'])->group(function () {
    Route::resource('customers', CustomerController::class);
});

// In Controller constructor
public function __construct()
{
    $this->middleware('log.sql')->only(['store', 'update']);
}
```

## Log File Examples

### laravel.log
```
[2025-06-19 10:30:45] local.INFO: User Activity {"action":"customer_created","user_id":1,"data":{"customer_id":123,"email":"john@example.com"},"ip":"127.0.0.1","user_agent":"Mozilla/5.0..."}

[2025-06-19 10:30:46] local.INFO: API Request {"method":"POST","url":"/api/customers","duration_ms":245,"memory_mb":45.2,"response_status":201}
```

### error.log
```
[2025-06-19 10:35:12] local.ERROR: Database connection failed {"exception":"Illuminate\\Database\\QueryException","message":"SQLSTATE[HY000] [2002] Connection refused","file":"/app/vendor/laravel/framework/src/Illuminate/Database/Connection.php","line":742}
```

### sql.log
```
[2025-06-19 10:30:45] local.DEBUG: SQL Query {"query":"select * from customers where email = ?","bindings":["john@example.com"],"time_ms":12.3,"request_id":"req_abc123"}

[2025-06-19 10:30:46] local.WARNING: Slow Query Detected {"query":"select * from customers left join orders on customers.id = orders.customer_id","time_ms":750,"request_id":"req_abc123","threshold_ms":500}
```

## Performance Monitoring

The package automatically monitors:

- **Request Duration**: Logs requests taking longer than 1 second
- **Memory Usage**: Warns when memory usage exceeds 100MB
- **SQL Queries**: Tracks query count and execution time per request
- **N+1 Detection**: Identifies potential N+1 query problems

## Log Management

### Viewing Logs

```bash
# View recent logs
./vendor/bin/sail exec app tail -f storage/logs/laravel.log
./vendor/bin/sail exec app tail -f storage/logs/error.log
./vendor/bin/sail exec app tail -f storage/logs/sql.log

# Search logs
./vendor/bin/sail exec app grep "customer_created" storage/logs/laravel.log
./vendor/bin/sail exec app grep "ERROR" storage/logs/error.log
```

### Log Rotation

Logs are automatically rotated daily and compressed after the retention period. You can manually trigger cleanup:

```bash
./vendor/bin/sail artisan log:cleanup
```

## Testing

Run the package tests:

```bash
# Run all Log package tests
./vendor/bin/sail test tests/Unit/Packages/Log/
./vendor/bin/sail test tests/Feature/Packages/Log/

# Run specific test
./vendor/bin/sail test --filter LogServiceTest
```

## Future CloudWatch Integration

The package is structured to support future CloudWatch integration:

1. **JSON Format**: Logs use structured JSON format compatible with CloudWatch
2. **Custom Fields**: Include request_id, user_id, and other CloudWatch-friendly fields
3. **Log Levels**: Proper log level mapping for CloudWatch filters
4. **Batching**: Ready for batch log shipping to CloudWatch

To prepare for CloudWatch:

1. Enable JSON formatting in production
2. Add CloudWatch log driver to `config/logging.php`
3. Configure CloudWatch credentials and log groups
4. Set up log shipping via AWS SDK or Fluentd

## Performance Considerations

- **Selective Logging**: Use `LOG_SQL=false` in production if not needed
- **Async Processing**: Consider queue-based logging for high-traffic applications
- **Disk Space**: Monitor disk usage with compression and retention settings
- **Memory**: LogService uses minimal memory footprint with lazy loading

## Security Notes

The package automatically excludes sensitive data:
- Passwords and tokens from request logs
- Credit card numbers and personal data
- Database connection strings from error logs

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure storage/logs directory is writable
```bash
./vendor/bin/sail exec app chmod -R 775 storage/logs
```

2. **Disk Space**: Check available disk space
```bash
./vendor/bin/sail exec app df -h
```

3. **Log Rotation Not Working**: Check cron jobs and file permissions

4. **Missing Logs**: Verify LOG_LEVEL and channel configuration

### Debug Mode

Enable debug mode for troubleshooting:

```env
LOG_LEVEL=debug
LOG_SQL=true
LOG_REQUESTS=true
```

## Contributing

1. Follow the coding standards in `CODING_RULES.md`
2. Write tests for new features
3. Update documentation
4. Use Laravel Sail for development: `./vendor/bin/sail`

## License

This package is part of the BoxPos application and follows the same license terms.
