# BoxPos Common Package

This package provides the core foundation for the BoxPos system, including multi-tenant store management, comprehensive logging, and base repository/service patterns.

## Features

### 1. Multi-Store Architecture
- **Store Context Management**: Automatic store scoping for all data operations
- **Store Switching**: Secure store switching with access validation
- **Permission Management**: Granular permissions per store per user
- **Store Isolation**: Complete data isolation between stores

### 2. Comprehensive Logging System
- **Activity Logging**: Track all user activities with context
- **Error Logging**: Detailed error tracking with stack traces
- **Performance Logging**: Monitor operation performance and identify bottlenecks
- **Security Logging**: Track security events and suspicious activities
- **Audit Trails**: Complete audit trails with before/after values

### 3. Repository + Service Pattern
- **BaseRepository**: Common CRUD operations with automatic store scoping
- **BaseService**: Transaction management and logging integration
- **StoreAwareModel**: Base model with automatic store context

### 4. Security Features
- **Store Access Control**: Validate user access to stores
- **Permission System**: Role-based permissions with store-level granularity
- **Audit Logging**: Complete audit trails for compliance
- **Session Management**: Secure session handling with store context

## Installation

1. Add the package to your Laravel application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/common"
        }
    ],
    "require": {
        "packages/common": "*"
    }
}
```

2. Install the package:

```bash
composer install
```

3. Register the service provider in `config/app.php`:

```php
'providers' => [
    // ...
    Packages\Common\CommonServiceProvider::class,
],
```

4. Publish and run migrations:

```bash
php artisan vendor:publish --tag=common-migrations
php artisan migrate
```

5. Publish configuration (optional):

```bash
php artisan vendor:publish --tag=common-config
```

## Configuration

The package can be configured via the `config/common.php` file:

```php
return [
    'store_context' => [
        'cache_ttl' => 300,
        'store_selection_route' => 'stores.select',
        'validate_context' => true,
    ],
    'logging' => [
        'retention_days' => 90,
        'database_logging' => true,
        'log_performance' => true,
    ],
    // ... more configuration options
];
```

## Usage

### Store Context Management

#### Using the StoreContext Helper

```php
use Packages\Common\Helpers\StoreContext;

// Get current store
$store = StoreContext::getCurrentStore();
$storeId = StoreContext::getCurrentStoreId();

// Check permissions
if (StoreContext::hasPermission('manage_inventory')) {
    // User can manage inventory
}

// Check specific capabilities
if (StoreContext::canProcessSales()) {
    // User can process sales
}

// Switch stores
StoreContext::switchStore($newStoreId);
```

#### Using the Middleware

Add the store context middleware to your routes:

```php
Route::middleware(['auth', 'store.context'])->group(function () {
    // Your protected routes here
});
```

#### Blade Directives

Use the provided Blade directives in your templates:

```blade
@storePermission('manage_inventory')
    <button>Manage Inventory</button>
@endstorePermission

@storeAdmin
    <button>Admin Panel</button>
@endstoreAdmin

<p>Current Store: @currentStore</p>

@canManageUsers
    <a href="/users">Manage Users</a>
@endcanManageUsers
```

### Repository Pattern

#### Creating a Repository

```php
use Packages\Common\Repositories\BaseRepository;

class ProductRepository extends BaseRepository
{
    // Inherits all base functionality with automatic store scoping
    
    public function findByCategory($categoryId)
    {
        return $this->newQuery()
            ->where('category_id', $categoryId)
            ->get();
    }
}
```

#### Creating a Service

```php
use Packages\Common\Services\BaseService;

class ProductService extends BaseService
{
    // Inherits transaction management and logging
    
    public function createProduct(array $data)
    {
        return $this->executeInTransaction(function () use ($data) {
            $product = $this->create($data);
            
            // Additional business logic here
            
            return $product;
        });
    }
}
```

#### Creating a Model

```php
use Packages\Common\Models\StoreAwareModel;

class Product extends StoreAwareModel
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'store_id', // Automatically managed
    ];
    
    // Model automatically scoped to current store
    // Audit logging enabled by default
}
```

### Logging

#### Using the Loggable Trait

```php
use Packages\Common\Traits\Loggable;

class MyService
{
    use Loggable;
    
    public function performAction()
    {
        $this->logActivity('action_started', ['context' => 'data']);
        
        try {
            // Your code here
            $this->logActivity('action_completed');
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'performAction']);
            throw $e;
        }
    }
}
```

#### Performance Logging

```php
$startTime = $this->startPerformanceTracking();

// Your code here

$this->endPerformanceTracking($startTime, 'operation_name', [
    'additional' => 'context'
]);
```

### Commands

#### Clean Old Logs

```bash
# Clean logs older than configured retention period
php artisan common:clean-logs

# Clean logs older than 30 days
php artisan common:clean-logs --days=30

# Dry run to see what would be deleted
php artisan common:clean-logs --dry-run
```

## Testing

The package includes comprehensive tests. Run them with:

```bash
# Run all tests
vendor/bin/phpunit packages/common/tests

# Run specific test suites
vendor/bin/phpunit packages/common/tests/Unit
vendor/bin/phpunit packages/common/tests/Integration
```

## Security Considerations

1. **Store Isolation**: All data is automatically scoped to the current store
2. **Permission Validation**: Always validate permissions before sensitive operations
3. **Audit Logging**: All activities are logged for compliance and security
4. **Session Security**: Store context is validated on every request

## Performance Considerations

1. **Caching**: Store access and permissions are cached for performance
2. **Query Optimization**: Automatic store scoping reduces query complexity
3. **Log Retention**: Configure appropriate log retention policies
4. **Database Indexing**: Ensure proper indexing on store_id columns

## Contributing

1. Follow PSR-12 coding standards
2. Write comprehensive tests for new features
3. Update documentation for any API changes
4. Ensure all tests pass before submitting

## License

This package is proprietary software for the BoxPos system.