# Log Package Installation Guide

This guide will help you install and configure the Log package in your BoxPos Laravel application.

## Step 1: Install Dependencies

The package should already be autoloaded since it's in your `composer.json`. If not, run:

```bash
./vendor/bin/sail composer dump-autoload
```

## Step 2: Publish Configuration

Publish the log configuration file:

```bash
./vendor/bin/sail artisan vendor:publish --tag=log-config
```

This will create `config/logging-package.php` with all configuration options.

## Step 3: Environment Configuration

Add these environment variables to your `.env` file:

```env
# Basic Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=debug
LOG_SQL=true
LOG_SLOW_QUERIES=true
SLOW_QUERY_THRESHOLD=500
LOG_REQUESTS=true
LOG_PERFORMANCE=true

# File Management
LOG_RETENTION_DAYS=14
LOG_COMPRESS=true
LOG_MAX_SIZE=100M

# Optional: Remote File Storage
LOG_TO_REMOTE_FILE=false
REMOTE_LOG_PATH=/var/log/shared/laravel-logs
REMOTE_LOG_MOUNT_POINT=/mnt/logs

# Optional: Slack Notifications
LOG_TO_SLACK=false
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# User Activity Logging
LOG_USER_ACTIVITY=true
LOG_ANONYMOUS_ACTIVITY=false
LOG_USER_IP=true
LOG_USER_AGENT=true

# Performance Monitoring
MEMORY_THRESHOLD_MB=100
EXECUTION_THRESHOLD_MS=1000
MAX_QUERIES_PER_REQUEST=50

# CloudWatch Preparation (for future use)
USE_JSON_FORMAT=false
INCLUDE_LOG_METADATA=true
CLOUDWATCH_LOG_GROUP_PREFIX=boxpos
```

## Step 4: Create Log Directories

Ensure the log directories exist and are writable:

```bash
./vendor/bin/sail exec app mkdir -p storage/logs
./vendor/bin/sail exec app chmod -R 775 storage/logs
./vendor/bin/sail exec app chown -R sail:sail storage/logs
```

## Step 5: Test the Installation

Create a test route to verify logging works:

```php
// In routes/web.php
Route::get('/test-logging', function () {
    $logService = app(\Packages\Log\Services\LogService::class);
    
    // Test different log types
    $logService->logUserActivity('test_activity', auth()->id(), ['test' => 'data']);
    $logService->logCustomEvent('test_event', ['message' => 'Hello World']);
    
    try {
        throw new Exception('Test exception');
    } catch (Exception $e) {
        $logService->logError($e, ['context' => 'testing']);
    }
    
    return 'Logging test completed. Check storage/logs/ for log files.';
});
```

## Step 6: Apply Middleware (Optional)

Add logging middleware to your routes:

```php
// In routes/web.php or routes/api.php
Route::middleware(['log.requests', 'log.performance'])->group(function () {
    // Your routes here
});

// Or in a controller constructor
public function __construct()
{
    $this->middleware('log.requests');
    $this->middleware('log.performance');
    $this->middleware('log.sql')->only(['store', 'update']);
}
```

## Step 7: Schedule Log Cleanup

Add log cleanup to your task scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up logs daily at 2 AM
    $schedule->command('log:cleanup')->dailyAt('02:00');
    
    // Or weekly cleanup
    $schedule->command('log:cleanup --days=30')->weekly();
}
```

## Step 8: Verify Log Files

After testing, you should see these log files in `storage/logs/`:

- `laravel-YYYY-MM-DD.log` - General application logs
- `error-YYYY-MM-DD.log` - Error logs only
- `sql-YYYY-MM-DD.log` - SQL query logs
- `performance-YYYY-MM-DD.log` - Performance metrics

## Step 9: View Logs

Use these commands to monitor your logs:

```bash
# View recent logs
./vendor/bin/sail exec app tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
./vendor/bin/sail exec app tail -f storage/logs/error-$(date +%Y-%m-%d).log
./vendor/bin/sail exec app tail -f storage/logs/sql-$(date +%Y-%m-%d).log

# Search logs
./vendor/bin/sail exec app grep "ERROR" storage/logs/error-*.log
./vendor/bin/sail exec app grep "customer_created" storage/logs/laravel-*.log
./vendor/bin/sail exec app grep "Slow Query" storage/logs/sql-*.log

# View log file sizes
./vendor/bin/sail exec app ls -lh storage/logs/
```

## Usage Examples

### In Controllers (using Loggable trait):

```php
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;

    public function store(Request $request)
    {
        try {
            $customer = Customer::create($request->validated());
            
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'email' => $customer->email
            ]);
            
            return response()->json($customer, 201);
        } catch (Exception $e) {
            $this->logError($e, ['action' => 'customer_creation']);
            throw $e;
        }
    }
}
```

### In Services (using LogsQueries trait):

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

### Direct LogService Usage:

```php
use Packages\Log\Services\LogService;

class SomeController extends Controller
{
    public function someAction(LogService $logService)
    {
        $logService->logUserActivity('action_performed', auth()->id(), [
            'action' => 'some_action',
            'data' => ['key' => 'value']
        ]);
    }
}
```

## Troubleshooting

### Common Issues:

1. **Permission Denied Errors**:
   ```bash
   ./vendor/bin/sail exec app chmod -R 775 storage/logs
   ./vendor/bin/sail exec app chown -R sail:sail storage/logs
   ```

2. **Log Files Not Created**:
   - Check LOG_LEVEL in .env
   - Verify logging is enabled in config
   - Check disk space: `./vendor/bin/sail exec app df -h`

3. **SQL Logs Not Appearing**:
   - Set `LOG_SQL=true` in .env
   - Check database connection
   - Verify SQL logging is enabled in config

4. **Performance Issues**:
   - Disable SQL logging in production: `LOG_SQL=false`
   - Increase LOG_RETENTION_DAYS to clean up more frequently
   - Enable log compression: `LOG_COMPRESS=true`

5. **Missing Service Provider**:
   ```bash
   ./vendor/bin/sail artisan config:clear
   ./vendor/bin/sail artisan cache:clear
   ./vendor/bin/sail composer dump-autoload
   ```

## Next Steps

1. **Configure Slack Notifications** (optional):
   - Set up Slack webhook URL
   - Configure critical error alerts

2. **Set up Remote File Storage** (optional):
   - Configure NFS or shared storage
   - Set REMOTE_LOG_PATH environment variable

3. **Prepare for CloudWatch** (optional):
   - Enable JSON formatting
   - Configure log batching
   - Set up AWS credentials

4. **Monitor Performance**:
   - Review slow query logs regularly
   - Monitor memory usage warnings
   - Check for N+1 query patterns

The Log package is now ready to use across all your packages (Customer, User, SessionManager, Appearance)!
