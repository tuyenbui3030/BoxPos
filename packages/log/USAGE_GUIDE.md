# Log Package - Hướng dẫn sử dụng

## Tổng quan

Package Log cung cấp hệ thống logging toàn diện cho BoxPos với các tính năng:

- ✅ **User Activity Logging** - Ghi log hoạt động người dùng
- ✅ **SQL Query Logging** - Ghi log tất cả SQL queries với performance
- ✅ **Request/Response Logging** - Ghi log HTTP requests/responses  
- ✅ **Performance Monitoring** - Theo dõi hiệu suất ứng dụng
- ✅ **Error Logging** - Ghi log lỗi với context chi tiết
- ✅ **Custom Events** - Ghi log custom events
- ✅ **Automatic Log Rotation** - Tự động xoay log files

## Cài đặt đã hoàn thành

Package đã được cài đặt và cấu hình:

1. ✅ Service Provider đã đăng ký trong `bootstrap/providers.php`
2. ✅ Middleware đã đăng ký trong `bootstrap/app.php`
3. ✅ Configuration đã được publish: `config/logging-package.php`
4. ✅ Environment variables đã được thêm vào `.env`

## Cách sử dụng

### 1. Trong Controllers

Sử dụng trait `Loggable`:

```php
<?php

namespace Packages\Customer\Http\Controllers;

use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;
    
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
        
        // Apply logging middleware
        $this->middleware('log.requests')->only(['store', 'update', 'destroy']);
        $this->middleware('log.sql')->only(['store', 'update']);
    }
    
    public function index(Request $request)
    {
        $customers = $this->customerService->searchCustomers(...);
        
        // Log user activity
        $this->logActivity('customers_viewed', [
            'search_term' => $request->get('search'),
            'results_count' => $customers->count(),
        ]);
        
        return CustomerResource::collection($customers);
    }
    
    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());
            
            // Log successful creation
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
            ]);
            
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            // Log error with context
            $this->logError($e, [
                'action' => 'customer_creation',
                'request_data' => $request->except(['password']),
            ]);
            
            throw $e;
        }
    }
}
```

### 2. Trong Services

```php
<?php

namespace Packages\Customer\Services;

use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable;
    
    public function createCustomer(array $data): Customer
    {
        $startTime = microtime(true);
        
        try {
            $customer = $this->customerRepository->create($data);
            
            // Log model event with changes
            $this->logModelEvent('created', $customer, [
                'created_fields' => array_keys($data),
            ]);
            
            // Log operation performance
            $this->logOperationPerformance('customer_creation', $startTime, [
                'customer_id' => $customer->id,
            ]);
            
            return $customer;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'customer_creation',
                'data' => $data,
            ]);
            
            throw $e;
        }
    }
}
```

### 3. Methods có sẵn trong Trait Loggable

```php
// Log user activity
$this->logActivity('action_name', $data, $userId);

// Log custom events  
$this->logEvent('event_name', $data);

// Log errors with context
$this->logError($exception, $context);

// Log info messages
$this->logInfo('message', $context);

// Log model events (create, update, delete)
$this->logModelEvent('updated', $model, $additionalData);

// Log operation performance
$this->logOperationPerformance('operation_name', $startTime, $context);

// Log process steps
$this->logProcessStep('process_name', 'step_name', $data);

// Log with query tracking
$result = $this->withQueryLogging('operation_name', function() {
    return $this->repository->someComplexQuery();
});

// Log with performance tracking
$result = $this->withPerformanceLogging('operation_name', function() {
    return $this->someExpensiveOperation();
});
```

### 4. Middleware sử dụng

```php
// Trong Controller constructor hoặc routes
$this->middleware('log.requests');     // Log HTTP requests/responses
$this->middleware('log.sql');          // Log SQL queries
$this->middleware('log.performance');  // Log performance metrics

// Trong routes
Route::middleware(['log.requests', 'log.performance'])->group(function () {
    Route::apiResource('customers', CustomerController::class);
});
```

### 5. Sử dụng LogService trực tiếp

```php
use Packages\Log\Services\LogService;

class SomeService
{
    protected LogService $logService;
    
    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }
    
    public function someMethod()
    {
        // Log user activity
        $this->logService->logUserActivity('action', $userId, $data);
        
        // Log custom event
        $this->logService->logCustomEvent('event', $data);
        
        // Log error
        $this->logService->logError($exception, $context);
        
        // Log SQL query
        $this->logService->logSqlQuery($sql, $time, $bindings);
    }
}
```

## Log Files được tạo

Package tạo các log files riêng biệt:

1. **`storage/logs/laravel-YYYY-MM-DD.log`** - Log chính với user activities, events
2. **`storage/logs/sql-YYYY-MM-DD.log`** - Tất cả SQL queries với performance
3. **`storage/logs/performance-YYYY-MM-DD.log`** - Performance metrics của requests
4. **`storage/logs/error-YYYY-MM-DD.log`** - Error logs với context chi tiết

## Configuration

File cấu hình: `config/logging-package.php`

### Environment Variables

```env
# SQL Query Logging
LOG_SQL=true
LOG_SLOW_QUERIES=true
SLOW_QUERY_THRESHOLD=500
DETECT_N_PLUS_ONE=true
MAX_QUERIES_PER_REQUEST=50

# Request Logging
LOG_REQUESTS=true
LOG_REQUEST_HEADERS=false
LOG_REQUEST_BODY=false

# Performance Monitoring
LOG_PERFORMANCE=true

# Log Retention
LOG_RETENTION_DAYS=14
```

## Examples thực tế

### Log một quy trình export phức tạp

```php
public function export(Request $request)
{
    // Log process start
    $this->logProcessStep('customer_export', 'started', [
        'export_format' => $request->get('format', 'csv'),
        'filters' => $request->only(['type', 'group', 'date_range']),
    ]);
    
    try {
        $exportData = $this->withQueryLogging('customer_export_query', function () use ($request) {
            return $this->customerService->exportCustomers($request->all());
        });
        
        $this->logProcessStep('customer_export', 'completed', [
            'records_exported' => count($exportData),
            'file_size_mb' => round(strlen(serialize($exportData)) / 1024 / 1024, 2),
        ]);
        
        return response()->json(['data' => $exportData]);
    } catch (\Exception $e) {
        $this->logProcessStep('customer_export', 'failed', [
            'error' => $e->getMessage(),
        ]);
        
        throw $e;
    }
}
```

### Log advanced search với performance

```php
public function search(Request $request)
{
    $startTime = microtime(true);
    
    try {
        $results = $this->customerService->advancedSearch($request->all());
        
        // Log search operation performance
        $this->logOperationPerformance('customer_search', $startTime, [
            'search_criteria' => $request->all(),
            'results_count' => $results->count(),
            'user_id' => auth()->id(),
        ]);
        
        return CustomerResource::collection($results);
    } catch (\Exception $e) {
        $this->logError($e, [
            'action' => 'customer_search',
            'search_criteria' => $request->all(),
        ]);
        
        throw $e;
    }
}
```

## Monitoring và Debugging

### Xem logs realtime

```bash
# Xem log chính
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log

# Xem SQL logs
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/sql-$(date +%Y-%m-%d).log

# Xem performance logs
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/performance-$(date +%Y-%m-%d).log
```

### Test package

```bash
# Test route đã tạo sẵn
curl http://localhost/test-log
```

## Best Practices

1. **Luôn sử dụng trait Loggable** trong Controllers và Services
2. **Log errors với context** để debug dễ dàng
3. **Log user activities** cho audit trail
4. **Monitor SQL queries** để tối ưu performance
5. **Sử dụng middleware** cho automatic logging
6. **Không log sensitive data** như passwords
7. **Sử dụng appropriate log levels** (info, warning, error)

## Tích hợp với monitoring tools

Package hỗ trợ:
- **Slack notifications** cho critical errors
- **Remote file logging** cho centralized logging
- **Custom channels** cho third-party services

Cấu hình trong `config/logging.php`:

```php
'channels' => [
    'slack_critical' => [
        'driver' => 'custom',
        'via' => Packages\Log\Channels\SlackChannel::class,
        'webhook' => env('SLACK_WEBHOOK_URL'),
        'level' => 'critical',
    ],
],
```

---

**Log package đã sẵn sàng sử dụng! 🎉**
