# Log Package - Quick Reference Card

## 🚀 Quick Setup

```bash
# Already configured in BoxPos!
# Check logs are working:
curl http://localhost/test-log
```

## 📝 Basic Usage

### In Controllers

```php
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;
    
    public function __construct()
    {
        // Apply middleware
        $this->middleware('log.requests')->only(['store', 'update']);
        $this->middleware('log.sql')->only(['store', 'update']);
    }
    
    public function index(Request $request)
    {
        $customers = $this->customerService->getCustomers();
        
        // Log user activity
        $this->logActivity('customers_viewed', [
            'count' => $customers->count(),
        ]);
        
        return CustomerResource::collection($customers);
    }
    
    public function store(Request $request)
    {
        try {
            $customer = $this->customerService->create($request->validated());
            
            // Log success
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
            ]);
            
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            // Log error
            $this->logError($e, [
                'action' => 'customer_creation',
                'data' => $request->validated(),
            ]);
            
            throw $e;
        }
    }
}
```

### In Services

```php
use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable;
    
    public function createCustomer(array $data): Customer
    {
        $startTime = microtime(true);
        
        try {
            $customer = $this->customerRepository->create($data);
            
            // Log model event
            $this->logModelEvent('created', $customer);
            
            // Log performance
            $this->logOperationPerformance('customer_creation', $startTime, [
                'customer_id' => $customer->id,
            ]);
            
            return $customer;
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'customer_creation']);
            throw $e;
        }
    }
}
```

## 🛠️ Available Methods

```php
// User activity logging
$this->logActivity('action_name', $data, $userId);

// Custom events
$this->logEvent('event_name', $data);

// Error logging with context
$this->logError($exception, $context);

// Info messages
$this->logInfo('message', $context);

// Model events (create, update, delete)
$this->logModelEvent('updated', $model, $additionalData);

// Performance tracking
$this->logOperationPerformance('operation_name', $startTime, $context);

// Process steps
$this->logProcessStep('process_name', 'step_name', $data);

// Query logging wrapper
$result = $this->withQueryLogging('operation_name', function() {
    return $this->repository->complexQuery();
});

// Performance logging wrapper
$result = $this->withPerformanceLogging('operation_name', function() {
    return $this->expensiveOperation();
});
```

## 🔧 Middleware

```php
// In Controller constructor
$this->middleware('log.requests');      // HTTP requests/responses
$this->middleware('log.sql');           // SQL queries
$this->middleware('log.performance');   // Performance metrics

// In routes
Route::middleware(['log.requests', 'log.performance'])->group(function () {
    Route::apiResource('customers', CustomerController::class);
});
```

## 📊 Log Files

```
storage/logs/
├── laravel-YYYY-MM-DD.log      # Main application logs
├── sql-YYYY-MM-DD.log          # SQL queries with performance
├── performance-YYYY-MM-DD.log  # Request performance metrics
└── error-YYYY-MM-DD.log        # Error logs with context
```

## 🔍 Monitoring Commands

```bash
# Monitor logs (without alias)
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log

# Monitor SQL logs (without alias)
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/sql-$(date +%Y-%m-%d).log

# With alias (after setup)
sail exec laravel.test tail -f /var/www/html/storage/logs/performance-$(date +%Y-%m-%d).log

# Clean up old logs
./vendor/bin/sail artisan log:cleanup --days=30
# With alias
sail artisan log:cleanup --days=30
```

## ⚙️ Configuration

### Environment Variables

```env
# SQL Query Logging
LOG_SQL=true
LOG_SLOW_QUERIES=true
SLOW_QUERY_THRESHOLD=500
DETECT_N_PLUS_ONE=true

# Request Logging
LOG_REQUESTS=true
LOG_REQUEST_HEADERS=false
LOG_REQUEST_BODY=false

# Performance Monitoring
LOG_PERFORMANCE=true
LOG_RETENTION_DAYS=14
```

## ✅ Best Practices

**DO:**
- ✅ Use `Loggable` trait in Controllers and Services
- ✅ Log user activities for audit trails
- ✅ Log errors with context
- ✅ Use appropriate log levels
- ✅ Monitor SQL query performance

**DON'T:**
- ❌ Log sensitive data (passwords, tokens)
- ❌ Log in loops without limits
- ❌ Ignore log performance impact
- ❌ Log everything - be selective

## 🧪 Test Logging

```bash
# Test the logging system
curl http://localhost/test-log

# Test Customer logging
curl http://localhost/test-customer-log
```

## 🚨 Common Patterns

### Complex Process Logging

```php
public function exportData(array $criteria): array
{
    $processId = uniqid('export_');
    
    $this->logProcessStep('data_export', 'started', [
        'process_id' => $processId,
        'criteria' => $criteria,
    ]);
    
    try {
        $data = $this->withQueryLogging("export_query_{$processId}", function () use ($criteria) {
            return $this->repository->getExportData($criteria);
        });
        
        $this->logProcessStep('data_export', 'completed', [
            'process_id' => $processId,
            'records_count' => count($data),
        ]);
        
        return $data;
    } catch (\Exception $e) {
        $this->logProcessStep('data_export', 'failed', [
            'process_id' => $processId,
            'error' => $e->getMessage(),
        ]);
        
        throw $e;
    }
}
```

### API Error Handling

```php
public function apiMethod(Request $request)
{
    try {
        $result = $this->service->performOperation($request->validated());
        
        $this->logActivity('api_success', [
            'endpoint' => $request->path(),
            'user_id' => auth()->id(),
        ]);
        
        return response()->json($result);
    } catch (\Exception $e) {
        $this->logError($e, [
            'endpoint' => $request->path(),
            'request_data' => $request->except(['password', 'token']),
            'user_id' => auth()->id(),
        ]);
        
        return response()->json(['error' => 'Operation failed'], 500);
    }
}
```

---

**📖 Full Documentation:** See `packages/Log/USAGE_GUIDE.md`

**🔧 Configuration:** `config/logging-package.php`

**🎯 Ready to use!** Log package is fully integrated into BoxPos architecture.
