# Store Package

This package provides comprehensive store/tenant management functionality for the BoxPos multi-tenant application.

## Architecture

The Store package follows Laravel best practices and clean architecture principles with a focus on multi-tenancy:

### Directory Structure

```
packages/Store/
├── src/
│   ├── Models/             # Store and UserStore models
│   ├── Builders/           # Query builders for complex queries
│   ├── Repositories/       # Data access layer
│   ├── Services/           # Business logic layer
│   ├── Events/             # Domain events
│   ├── Exceptions/         # Custom exceptions
│   └── Database/
│       └── Migrations/     # Database migrations
├── config/                 # Package configuration
├── routes/                 # Package routes
└── resources/
    └── views/              # Blade templates
```

## Features

- ✅ **Multi-Store Management** - Create and manage multiple stores/tenants
- ✅ **User-Store Relationships** - Many-to-many relationships with roles and permissions
- ✅ **Role-Based Access Control** - Admin, Manager, Staff, and Viewer roles
- ✅ **Store Settings** - Configurable store settings and preferences
- ✅ **Store Status Management** - Active, Inactive, and Suspended states
- ✅ **Domain Management** - Custom domains for stores
- ✅ **Comprehensive Logging** - All operations are logged with context
- ✅ **Event System** - Domain events for store operations

## Models

### Store Model

The main store/tenant model with the following key features:

- **Basic Information**: name, slug, domain, description
- **Contact Details**: address, phone, email
- **Localization**: timezone, currency, language
- **Settings**: JSON field for flexible configuration
- **Status Management**: active, inactive, suspended

### UserStore Model

Pivot model for user-store relationships with:

- **Role Management**: admin, manager, staff, viewer
- **Permissions**: JSON array of specific permissions
- **Status**: active/inactive relationship status
- **Audit Trail**: joined_at timestamp

## Services

### StoreService

Main business logic service providing:

```php
// Create store
$store = $storeService->createStore([
    'name' => 'My Store',
    'email' => 'store@example.com',
    'timezone' => 'Asia/Ho_Chi_Minh',
    'currency' => 'VND',
]);

// Add user to store
$storeService->addUserToStore($store, $userId, 'manager');

// Check permissions
$hasPermission = $storeService->userHasPermission($store, $userId, 'manage_products');

// Get user's stores
$stores = $storeService->getStoresForUser($userId);
```

## Repository Pattern

### StoreRepository

Data access layer with advanced querying:

```php
// Search stores
$stores = $storeRepository->search([
    'search' => 'electronics',
    'status' => 'active',
    'user_id' => 123,
]);

// Get stores for user
$userStores = $storeRepository->getForUser($userId);

// Check user access
$hasAccess = $storeRepository->userHasAccess($store, $userId);
```

## Builder Pattern

### StoreBuilder

Fluent query builder for complex queries:

```php
// Chain query methods
$stores = Store::query()
    ->active()
    ->forUser($userId)
    ->withActiveUsers()
    ->search('electronics')
    ->orderByName()
    ->get();

// Apply filters
$stores = Store::query()
    ->applyFilters([
        'search' => 'store name',
        'status' => 'active',
        'user_id' => 123,
    ])
    ->paginate(15);

// Business scenarios
$stores = Store::query()
    ->forScenario('user_accessible')
    ->get();
```

## Events

The package dispatches the following events:

- `StoreCreated` - When a new store is created
- `StoreUpdated` - When store information is updated
- `StoreDeleted` - When a store is deleted
- `UserAddedToStore` - When a user is added to a store
- `UserRemovedFromStore` - When a user is removed from a store

## Roles and Permissions

### Available Roles

1. **Admin** - Full access to all store features
2. **Manager** - Manage operations and view reports
3. **Staff** - Handle customer orders and basic operations
4. **Viewer** - Read-only access to dashboard

### Available Permissions

- `view_dashboard` - View store dashboard
- `manage_customers` - Create, edit, delete customers
- `manage_products` - Manage product catalog
- `manage_orders` - Process and manage orders
- `manage_inventory` - Track and manage inventory
- `manage_reports` - Access reports and analytics
- `manage_settings` - Configure store settings
- `manage_users` - Manage store users and permissions

## Configuration

The package includes comprehensive configuration in `config/store.php`:

```php
// Default store settings
'defaults' => [
    'status' => 'active',
    'timezone' => 'UTC',
    'currency' => 'USD',
    'language' => 'en',
],

// Role definitions
'roles' => [
    'admin' => [
        'name' => 'Administrator',
        'permissions' => [...],
    ],
],

// Supported currencies and languages
'currencies' => [...],
'languages' => [...],
```

## Database Schema

### Stores Table

```sql
CREATE TABLE stores (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    domain VARCHAR(255) UNIQUE,
    logo VARCHAR(255),
    description TEXT,
    address TEXT,
    phone VARCHAR(255),
    email VARCHAR(255),
    timezone VARCHAR(255) DEFAULT 'UTC',
    currency VARCHAR(3) DEFAULT 'USD',
    language VARCHAR(2) DEFAULT 'en',
    settings JSON,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### User Stores Table

```sql
CREATE TABLE user_stores (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE CASCADE,
    store_id BIGINT REFERENCES stores(id) ON DELETE CASCADE,
    role ENUM('admin', 'manager', 'staff', 'viewer') DEFAULT 'staff',
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    joined_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(user_id, store_id)
);
```

## Usage Examples

### Creating a Store

```php
use Packages\Store\Services\StoreService;

$storeService = app(StoreService::class);

$store = $storeService->createStore([
    'name' => 'Electronics Store',
    'email' => 'contact@electronics.com',
    'phone' => '+1234567890',
    'address' => '123 Main St, City, Country',
    'timezone' => 'America/New_York',
    'currency' => 'USD',
    'language' => 'en',
    'settings' => [
        'allow_guest_checkout' => true,
        'tax_rate' => 8.5,
    ],
]);
```

### Managing Users

```php
// Add user as manager
$storeService->addUserToStore($store, $userId, 'manager');

// Update user role
$storeService->updateUserRole($store, $userId, 'admin');

// Remove user from store
$storeService->removeUserFromStore($store, $userId);

// Check permissions
if ($storeService->userHasPermission($store, $userId, 'manage_products')) {
    // User can manage products
}
```

### Querying Stores

```php
// Get all stores for a user
$userStores = $storeService->getStoresForUser($userId);

// Search stores
$stores = $storeService->searchStores([
    'search' => 'electronics',
    'status' => 'active',
], 15); // 15 per page

// Get store by slug
$store = $storeService->getStoreBySlug('electronics-store');
```

## Integration with Multi-Tenant System

This package is designed to work seamlessly with the Tenant package for complete multi-tenancy:

1. **Store Context** - Each request is scoped to a specific store
2. **Data Isolation** - All data is automatically filtered by store_id
3. **User Switching** - Users can switch between stores they have access to
4. **Permission Checking** - Role-based permissions per store

## Testing

The package includes comprehensive tests:

```bash
# Run store package tests
./vendor/bin/sail test packages/store/tests/

# Run specific test
./vendor/bin/sail test --filter StoreServiceTest
```

## Dependencies

- Laravel Framework ^12.0
- Packages\Log (for logging functionality)
- Packages\User (for user relationships)

## Security Features

- **Access Control** - Role-based permissions per store
- **Data Isolation** - Automatic store-based data filtering
- **Audit Logging** - All operations are logged with context
- **Input Validation** - Comprehensive validation rules
- **Event Tracking** - Domain events for important actions

## Performance Considerations

- **Database Indexes** - Optimized indexes for common queries
- **Query Optimization** - Efficient queries using Builder pattern
- **Caching** - Configurable caching for store data
- **Lazy Loading** - Relationships loaded only when needed
