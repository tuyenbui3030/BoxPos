# Tenant Package

This package provides comprehensive multi-tenancy functionality for the BoxPos application, enabling data isolation and context switching between stores.

## Architecture

The Tenant package follows Laravel best practices and integrates seamlessly with the Store package to provide complete multi-tenant functionality.

### Directory Structure

```
packages/Tenant/
├── src/
│   ├── Services/           # Tenant business logic
│   ├── Middleware/         # Tenant context and data isolation
│   ├── Traits/             # Tenant scope trait for models
│   ├── Scopes/             # Global scope for automatic filtering
│   ├── Facades/            # Tenant facade for easy access
│   └── Exceptions/         # Custom exceptions
├── config/                 # Package configuration
└── README.md
```

## Features

- ✅ **Automatic Data Isolation** - Global scopes filter data by current store
- ✅ **Context Switching** - Seamless switching between stores
- ✅ **Permission Management** - Store-based role and permission checking
- ✅ **Middleware Integration** - Automatic tenant context setup
- ✅ **Facade Support** - Easy access through Tenant facade
- ✅ **Comprehensive Logging** - All tenant operations are logged

## Core Components

### TenantService

The main service for managing tenant context:

```php
use Packages\Tenant\Facades\Tenant;

// Get current store
$store = Tenant::getCurrentStore();

// Switch to different store
Tenant::switchStore($storeId);

// Check permissions
if (Tenant::userHasPermission('manage_products')) {
    // User can manage products in current store
}

// Check roles
if (Tenant::isAdmin()) {
    // User is admin in current store
}

// Get user's accessible stores
$stores = Tenant::getUserStores();
```

### HasTenantScope Trait

Add to models that should be tenant-scoped:

```php
use Packages\Tenant\Traits\HasTenantScope;

class Customer extends Model
{
    use HasTenantScope;
    
    // Model will automatically be filtered by current store
    // store_id will be automatically set when creating
}

// Usage
$customers = Customer::all(); // Only current store's customers
$allCustomers = Customer::withoutTenantScope()->get(); // All customers
$storeCustomers = Customer::forStore($storeId)->get(); // Specific store
```

### Middleware

#### TenantContext Middleware

Automatically sets up tenant context for each request:

```php
// In routes or middleware groups
Route::middleware(['auth', 'tenant.context'])->group(function () {
    // Routes that require tenant context
});
```

#### TenantDataIsolation Middleware

Applies global scopes for automatic data filtering:

```php
// In routes or middleware groups
Route::middleware(['auth', 'tenant.isolation'])->group(function () {
    // Routes with automatic data isolation
});
```

## Configuration

Configure tenant behavior in `config/tenant.php`:

```php
return [
    // Tenant column name
    'tenant_column' => 'store_id',
    
    // Auto apply tenant scope
    'auto_apply_scope' => true,
    
    // Models to be tenant-scoped
    'scoped_models' => [
        \Packages\Customer\Models\Customer::class,
        // Add other models here
    ],
    
    // Routes excluded from tenant context
    'excluded_routes' => [
        'store.selection',
        'store.switch',
        'api.*',
    ],
];
```

## Usage Examples

### Basic Tenant Operations

```php
use Packages\Tenant\Facades\Tenant;

// Check if user has tenant context
if (Tenant::hasTenantContext()) {
    $store = Tenant::getCurrentStore();
    echo "Current store: " . $store->name;
}

// Switch to different store
try {
    $newStore = Tenant::switchStore($storeId);
    echo "Switched to: " . $newStore->name;
} catch (StoreAccessDeniedException $e) {
    echo "Access denied to store";
}

// Require specific permission
try {
    Tenant::requirePermission('manage_products');
    // User has permission, continue
} catch (StoreAccessDeniedException $e) {
    // User doesn't have permission
}
```

### Model Integration

```php
// Add trait to your model
class Product extends Model
{
    use HasTenantScope;
    
    protected $fillable = ['name', 'price', 'store_id'];
}

// Usage - automatic tenant filtering
$products = Product::all(); // Only current store's products

// Create new product - store_id automatically set
$product = Product::create([
    'name' => 'New Product',
    'price' => 99.99,
    // store_id automatically set to current store
]);

// Query specific store
$storeProducts = Product::forStore($storeId)->get();

// Query multiple stores
$multiStoreProducts = Product::forStores([$store1, $store2])->get();

// Bypass tenant filtering
$allProducts = Product::withoutTenantScope()->get();
```

### Permission Checking

```php
// Check specific permission
if (Tenant::userHasPermission('manage_customers')) {
    // User can manage customers
}

// Check role
if (Tenant::isAdmin()) {
    // User is admin in current store
}

if (Tenant::isManager()) {
    // User is admin or manager
}

// Get user's role
$role = Tenant::getUserRole(); // 'admin', 'manager', 'staff', 'viewer'

// Check for another user
if (Tenant::userHasPermission('manage_orders', $userId)) {
    // Specific user has permission
}
```

### Store Context

```php
// Get detailed store context
$context = Tenant::getStoreContext();
/*
[
    'store_id' => 1,
    'store_name' => 'Main Store',
    'store_slug' => 'main-store',
    'user_id' => 123,
    'user_role' => 'admin',
    'session_store_id' => 1,
]
*/

// Initialize tenant context manually
Tenant::initializeTenantContext();

// Reset tenant context
Tenant::resetTenantContext();
```

## Middleware Setup

Add middleware to your application:

```php
// In bootstrap/app.php or App\Http\Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Packages\Tenant\Middleware\TenantContext::class,
        \Packages\Tenant\Middleware\TenantDataIsolation::class,
    ],
];

// Or use middleware aliases
protected $middlewareAliases = [
    'tenant.context' => \Packages\Tenant\Middleware\TenantContext::class,
    'tenant.isolation' => \Packages\Tenant\Middleware\TenantDataIsolation::class,
];
```

## Database Migrations

Add store_id to your tenant-scoped tables:

```php
Schema::table('customers', function (Blueprint $table) {
    $table->foreignId('store_id')->constrained()->onDelete('cascade');
    $table->index(['store_id']);
});
```

## Error Handling

The package provides specific exceptions:

```php
use Packages\Tenant\Exceptions\NoCurrentStoreException;
use Packages\Tenant\Exceptions\StoreAccessDeniedException;

try {
    $store = Tenant::requireCurrentStore();
} catch (NoCurrentStoreException $e) {
    // No current store set
    return redirect()->route('store.selection');
}

try {
    Tenant::requirePermission('manage_settings');
} catch (StoreAccessDeniedException $e) {
    // User doesn't have permission
    abort(403, 'Access denied');
}
```

## Integration with Other Packages

### Customer Package Integration

```php
// Customer model automatically tenant-scoped
class Customer extends Model
{
    use HasTenantScope;
    
    // Customers automatically filtered by current store
}

// Repository methods work with tenant context
$customers = $customerRepository->getAll(); // Only current store's customers
```

### Store Package Integration

The Tenant package works seamlessly with the Store package:

```php
// Get user's stores
$stores = Tenant::getUserStores();

// Switch between stores
foreach ($stores as $store) {
    if (Tenant::userHasAccessToStore(auth()->id(), $store)) {
        Tenant::switchStore($store->id);
        break;
    }
}
```

## Performance Considerations

- **Global Scopes**: Automatically applied for data isolation
- **Caching**: Store context is cached per request
- **Database Indexes**: Ensure store_id columns are indexed
- **Query Optimization**: Use appropriate scoping methods

## Security Features

- **Automatic Data Isolation**: Global scopes prevent cross-tenant data access
- **Permission Checking**: Role-based permissions per store
- **Access Control**: Verify user access before store switching
- **Audit Logging**: All tenant operations are logged

## Testing

```php
// Test tenant functionality
use Packages\Tenant\Facades\Tenant;

public function test_user_can_switch_stores()
{
    $user = User::factory()->create();
    $store = Store::factory()->create();
    
    // Add user to store
    $store->users()->attach($user->id, ['role' => 'admin']);
    
    // Switch to store
    $this->actingAs($user);
    Tenant::switchStore($store->id);
    
    $this->assertEquals($store->id, Tenant::getCurrentStoreId());
}
```

## Dependencies

- Laravel Framework ^12.0
- Packages\Store (for store management)
- Packages\Log (for logging functionality)
