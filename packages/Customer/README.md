# Customer Package

This package provides comprehensive customer management functionality for the BoxPos2 application.

## Architecture

The Customer package follows Laravel best practices and clean architecture principles:

### Directory Structure

```
packages/Customer/
├── src/
│   ├── Services/           # Business logic layer
│   ├── Repositories/       # Data access layer
│   ├── Http/
│   │   ├── Controllers/    # HTTP request handling
│   │   ├── Requests/       # Form validation
│   │   ├── Resources/      # API response formatting
│   │   └── Middleware/     # Request middleware
│   ├── Events/            # Domain events
│   ├── Listeners/         # Event handlers
│   ├── Jobs/              # Background jobs
│   ├── Exceptions/        # Custom exceptions
│   ├── Tests/             # Unit and feature tests
│   └── Models/            # Eloquent models
├── config/                # Package configuration
├── resources/
│   └── views/             # Blade templates
└── routes/                # Package routes
```

## Components

### Services
- **CustomerService**: Main business logic for customer operations
  - CRUD operations with business rules
  - Event dispatching
  - Transaction handling

### Repositories
- **CustomerRepository**: Data access abstraction
  - Database queries
  - Caching support
  - Search and filtering

### Form Requests
- **StoreCustomerRequest**: Validation for creating customers
- **UpdateCustomerRequest**: Validation for updating customers

### API Resources
- **CustomerResource**: API response formatting

### Events & Listeners
- **CustomerCreated** → SendWelcomeEmail
- **CustomerUpdated** → LogCustomerUpdate
- **CustomerDeleted** → (Various cleanup listeners)

### Jobs
- **ExportCustomersJob**: Background customer data export

### Middleware
- **EnsureCustomerOwnership**: Ensures users can only access their customers
- **LogCustomerActions**: Logs all customer-related API calls
- **CustomerRateLimiter**: Rate limiting for customer operations

### Exceptions
- **CustomerNotFoundException**: When customer doesn't exist
- **CustomerValidationException**: For business rule violations

## Usage Examples

### Basic CRUD Operations

```php
use Packages\Customer\Services\CustomerService;

// Inject service via dependency injection
public function __construct(private CustomerService $customerService) {}

// Create a customer
$customer = $this->customerService->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
    'status' => 'active'
]);

// Update a customer
$customer = $this->customerService->update($customerId, [
    'name' => 'Jane Doe',
    'status' => 'inactive'
]);

// Get customers with pagination
$customers = $this->customerService->getAllCustomers($perPage = 15);

// Search customers
$customers = $this->customerService->searchCustomers('john');
```

### Using Repository Directly

```php
use Packages\Customer\Repositories\CustomerRepository;

// Inject repository
public function __construct(private CustomerRepository $customerRepository) {}

// Find by email
$customer = $this->customerRepository->findByEmail('john@example.com');

// Get active customers
$activeCustomers = $this->customerRepository->getByStatus('active');
```

## Configuration

The package includes a comprehensive configuration file at `config/customer.php`:

```php
return [
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
    'validation' => [
        'email_unique' => true,
        'phone_format' => 'international',
    ],
    'features' => [
        'welcome_email' => true,
        'activity_logging' => true,
        'export_jobs' => true,
    ],
    // ... more options
];
```

## Events

The package dispatches several events that you can listen to:

- `CustomerCreated`: When a new customer is created
- `CustomerUpdated`: When customer data is modified
- `CustomerDeleted`: When a customer is removed

## Testing

Run package tests:

```bash
php artisan test packages/Customer/src/Tests
```

### Test Coverage
- **Unit Tests**: Service and repository logic
- **Feature Tests**: API endpoints and integration

## Middleware Usage

Apply middleware to routes:

```php
Route::middleware(['customer.ownership', 'customer.logging'])->group(function () {
    Route::apiResource('customers', CustomerController::class);
});
```

## API Resources

Format API responses consistently:

```php
use Packages\Customer\Http\Resources\CustomerResource;

return CustomerResource::make($customer);
// or for collections
return CustomerResource::collection($customers);
```

## Error Handling

The package provides custom exceptions:

```php
try {
    $customer = $customerService->findById($id);
} catch (CustomerNotFoundException $e) {
    return response()->json(['error' => 'Customer not found'], 404);
} catch (CustomerValidationException $e) {
    return response()->json(['error' => $e->getMessage()], 422);
}
```

## Background Jobs

Export customer data asynchronously:

```php
use Packages\Customer\Jobs\ExportCustomersJob;

// Dispatch export job
ExportCustomersJob::dispatch($filters, $format = 'csv');
```

## Views

The package includes responsive Blade templates:
- `index.blade.php`: Customer listing with search and filters
- `create.blade.php`: Customer creation form
- `show.blade.php`: Customer detail view
- `edit.blade.php`: Customer editing form

## Security Features

- **Rate Limiting**: Prevents API abuse
- **Access Control**: Ownership-based access
- **Input Validation**: Comprehensive form validation
- **Activity Logging**: All actions are logged

## Dependencies

- Laravel Framework ^12.0
- Laravel Livewire ^3.6 (for interactive components)

## License

This package is part of the BoxPos2 application and follows the same license terms.
