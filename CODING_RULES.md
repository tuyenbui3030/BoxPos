# Coding Rules & Architecture Guidelines

## Table of Contents
- [Architecture Overview](#architecture-overview)
- [Layer Responsibilities](#layer-responsibilities)
- [Repository Pattern](#repository-pattern)
- [Builder Pattern](#builder-pattern)
- [Service Layer](#service-layer)
- [Controller Guidelines](#controller-guidelines)
- [Livewire Components](#livewire-components)
- [Code Quality Standards](#code-quality-standards)
- [Testing Guidelines](#testing-guidelines)
- [Package Structure](#package-structure)

## Architecture Overview

Our application follows a **Repository + Builder Pattern** architecture with clear separation of concerns:

```
Controller → Service → Repository → Builder → Model → Database
```

### Key Principles
1. **Single Responsibility** - Each class has one clear purpose
2. **Dependency Injection** - Use constructor injection for dependencies
3. **Interface Segregation** - Keep interfaces focused and minimal
4. **Don't Repeat Yourself (DRY)** - Reuse code through proper abstraction
5. **SOLID Principles** - Follow all SOLID design principles

## Layer Responsibilities

### 1. Controllers
- Handle HTTP requests and responses
- Validate input data (using Form Requests)
- Call appropriate Service methods
- Return proper HTTP responses (JSON/Views)
- **NEVER** directly access Models or Repositories

```php
// ✅ Good
public function index(Request $request)
{
    $customers = $this->customerService->searchCustomers(
        $request->get('search', ''),
        $request->only(['type', 'group', 'gender']),
        $request->get('per_page', 15)
    );
    
    return $request->expectsJson() 
        ? CustomerResource::collection($customers)
        : view('customers.index', compact('customers'));
}

// ❌ Bad
public function index()
{
    $customers = Customer::where('status', 'active')->get();
    return view('customers.index', compact('customers'));
}
```

### 2. Services
- Contain business logic and complex operations
- Handle transactions and data consistency
- Coordinate between multiple repositories
- Dispatch events for important actions
- **NEVER** directly query the database

```php
// ✅ Good
public function createCustomer(array $data): Customer
{
    $customer = $this->customerRepository->create($data);
    
    // Dispatch business event
    event(new CustomerCreated($customer));
    
    return $customer;
}

// ❌ Bad
public function createCustomer(array $data): Customer
{
    return Customer::create($data);
}
```

### 3. Repositories
- Abstract data access operations
- Use Builder Pattern for complex queries
- Handle caching and data transformation
- Provide clean interface for data operations
- **NEVER** contain business logic

```php
// ✅ Good
public function getVipCustomers(float $minSales, float $maxDebt): Collection
{
    return $this->model->query()
        ->vipCustomers($minSales, $maxDebt)
        ->withCreator()
        ->orderByHighestSales()
        ->get();
}

// ❌ Bad
public function getVipCustomers(): Collection
{
    $customers = $this->model->where('total_sales', '>', 10000)->get();
    
    // Business logic doesn't belong here
    foreach ($customers as $customer) {
        $customer->sendWelcomeEmail();
    }
    
    return $customers;
}
```

### 4. Builders
- Provide fluent interface for query construction
- Encapsulate complex query logic
- Enable method chaining for flexibility
- Keep queries maintainable and readable

```php
// ✅ Good
public function vipCustomers(float $minSales = 10000, float $maxDebt = 1000): self
{
    return $this->salesAbove($minSales)->debtBelow($maxDebt);
}

public function salesAbove(float $amount): self
{
    return $this->where('total_sales', '>', $amount);
}
```

## Repository Pattern

### Implementation Rules

1. **One Repository per Model** - Each model should have its own repository
2. **Interface-based** - Define repository interfaces for better testability
3. **Dependency Injection** - Inject repositories into services
4. **Builder Integration** - Use Builder Pattern for complex queries

### Repository Structure

```php
namespace Packages\Customer\Repositories;

use Packages\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    protected Customer $model;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    // Basic CRUD operations
    public function create(array $data): Customer { }
    public function findById(int $id): ?Customer { }
    public function update(Customer $customer, array $data): Customer { }
    public function delete(Customer $customer): bool { }

    // Complex queries using Builder Pattern
    public function getVipCustomers(float $minSales, float $maxDebt): Collection
    {
        return $this->model->query()
            ->vipCustomers($minSales, $maxDebt)
            ->get();
    }
}
```

## Builder Pattern

### Implementation Rules

1. **Extend Eloquent Builder** - Custom builders should extend `Illuminate\Database\Eloquent\Builder`
2. **Method Chaining** - All methods should return `self` for chaining
3. **Descriptive Names** - Method names should clearly describe their purpose
4. **Reusable Logic** - Create small, composable methods

### Builder Structure

```php
namespace Packages\Customer\Builders;

use Illuminate\Database\Eloquent\Builder;

class CustomerBuilder extends Builder
{
    // Basic filters
    public function search(string $term): self
    {
        return $this->where(function ($query) use ($term) {
            $query->where('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_code', 'like', "%{$term}%")
                  ->orWhere('phone_number', 'like', "%{$term}%");
        });
    }

    // Business logic methods
    public function vipCustomers(float $minSales = 10000, float $maxDebt = 1000): self
    {
        return $this->salesAbove($minSales)->debtBelow($maxDebt);
    }

    // Composable building blocks
    public function salesAbove(float $amount): self
    {
        return $this->where('total_sales', '>', $amount);
    }
}
```

### Model Integration

```php
namespace Packages\Customer\Models;

use Packages\Customer\Builders\CustomerBuilder;

class Customer extends Model
{
    public function newEloquentBuilder($query)
    {
        return new CustomerBuilder($query);
    }
}
```

## Service Layer

### Rules

1. **Business Logic Only** - Services contain business rules and workflows
2. **Transaction Management** - Handle database transactions in services
3. **Event Dispatching** - Dispatch domain events for important actions
4. **Repository Coordination** - Coordinate multiple repositories when needed

### Service Structure

```php
namespace Packages\Customer\Services;

class CustomerService
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function createCustomer(array $data): Customer
    {
        DB::beginTransaction();
        
        try {
            $data['customer_code'] = Customer::generateCustomerCode();
            $customer = $this->customerRepository->create($data);
            
            // Business logic
            event(new CustomerCreated($customer));
            
            DB::commit();
            return $customer;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
```

## Controller Guidelines

### Rules

1. **Thin Controllers** - Keep controllers as thin as possible
2. **Form Requests** - Use Form Request classes for validation
3. **Resource Classes** - Use API Resource classes for JSON responses
4. **Exception Handling** - Handle exceptions gracefully with proper HTTP codes

### Controller Structure

```php
namespace Packages\Customer\Http\Controllers;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());

            return $request->expectsJson()
                ? new CustomerResource($customer)
                : redirect()->route('customers.show', $customer)
                    ->with('success', 'Customer created successfully');
        } catch (\Exception $e) {
            return $request->expectsJson()
                ? response()->json(['error' => 'Failed to create customer'], 500)
                : back()->withInput()->with('error', 'Failed to create customer');
        }
    }
}
```

## Livewire Components

### Rules

1. **UI Logic Only** - Components handle UI state and user interactions
2. **Builder Pattern Usage** - Use Builder Pattern for filtering and searching
3. **Property Validation** - Validate component properties
4. **Event Handling** - Use Livewire events for component communication

### Livewire Structure

```php
namespace Packages\Customer\Livewire;

class CustomerManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filters = [];

    public function getCustomersProperty()
    {
        return Customer::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filters['type'] ?? null, fn($q) => $q->byType($this->filters['type']))
            ->when($this->filters['has_debt'] ?? false, fn($q) => $q->withDebt())
            ->orderByName()
            ->paginate(15);
    }
}
```

## Code Quality Standards

### Naming Conventions

1. **Classes** - PascalCase (`CustomerService`, `CustomerBuilder`)
2. **Methods** - camelCase (`getCustomers`, `searchByName`)
3. **Variables** - camelCase (`$customerData`, `$totalSales`)
4. **Constants** - SCREAMING_SNAKE_CASE (`MAX_RETRY_ATTEMPTS`)
5. **Database** - snake_case (`customer_name`, `total_sales`)

### Documentation

1. **PHPDoc Comments** - All public methods must have proper PHPDoc
2. **Type Hints** - Use strict type hints for parameters and return types
3. **README Files** - Each package should have comprehensive README

```php
/**
 * Search customers by criteria using Builder Pattern
 *
 * @param string $search Search term for name, code, or phone
 * @param array $filters Additional filters (type, group, gender, etc.)
 * @param int $perPage Number of items per page
 * @return LengthAwarePaginator
 * @throws \Exception When search fails
 */
public function searchCustomers(string $search = '', array $filters = [], int $perPage = 15): LengthAwarePaginator
{
    // Implementation
}
```

### Error Handling

1. **Custom Exceptions** - Create domain-specific exceptions
2. **Proper HTTP Codes** - Return appropriate HTTP status codes
3. **Logging** - Log errors with proper context
4. **User-Friendly Messages** - Provide clear error messages

```php
// Custom exception
class CustomerNotFoundException extends \Exception
{
    public function __construct(string $message = 'Customer not found')
    {
        parent::__construct($message);
    }
}

// Usage in service
public function getCustomerById(int $id): Customer
{
    $customer = $this->customerRepository->findById($id);
    
    if (!$customer) {
        throw new CustomerNotFoundException("Customer with ID {$id} not found");
    }
    
    return $customer;
}
```

## Testing Guidelines

### Rules

1. **Unit Tests** - Test individual classes in isolation
2. **Feature Tests** - Test complete features end-to-end
3. **Mock Dependencies** - Mock external dependencies in unit tests
4. **Test Coverage** - Maintain high test coverage (>80%)

### Test Structure

```php
namespace Tests\Unit\Packages\Customer\Services;

class CustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerService $customerService;
    protected CustomerRepository $customerRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->customerRepository = Mockery::mock(CustomerRepository::class);
        $this->customerService = new CustomerService($this->customerRepository);
    }

    public function test_creates_customer_successfully()
    {
        $data = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $customer = new Customer($data);
        
        $this->customerRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($customer);
        
        $result = $this->customerService->createCustomer($data);
        
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertEquals('John Doe', $result->name);
    }
}
```

## Package Structure

### Standard Package Structure

```
packages/
├── Customer/
│   ├── config/
│   │   └── customer.php
│   ├── resources/
│   │   ├── views/
│   │   └── lang/
│   ├── routes/
│   │   ├── web.php
│   │   └── api.php
│   └── src/
│       ├── Builders/
│       │   └── CustomerBuilder.php
│       ├── Http/
│       │   ├── Controllers/
│       │   ├── Requests/
│       │   └── Resources/
│       ├── Livewire/
│       ├── Models/
│       │   └── Customer.php
│       ├── Repositories/
│       │   └── CustomerRepository.php
│       ├── Services/
│       │   └── CustomerService.php
│       ├── Events/
│       ├── Listeners/
│       ├── Jobs/
│       ├── Exceptions/
│       └── CustomerServiceProvider.php
```

### Package Rules

1. **Self-Contained** - Each package should be self-contained
2. **Service Provider** - Each package must have a service provider
3. **Configuration** - Package-specific configuration files
4. **Migrations** - Database migrations within packages
5. **Tests** - Package-specific tests

## Best Practices Summary

### DO's ✅

1. Use dependency injection for all dependencies
2. Follow the Repository + Builder Pattern architecture
3. Keep controllers thin and focused
4. Use Builder Pattern for complex queries
5. Write comprehensive tests
6. Document public methods with PHPDoc
7. Use type hints for all parameters and return types
8. Handle exceptions gracefully
9. Follow PSR standards
10. Use meaningful variable and method names

### DON'Ts ❌

1. Don't bypass the service layer in controllers
2. Don't put business logic in repositories
3. Don't directly query models in controllers
4. Don't ignore exceptions
5. Don't use magic numbers or strings
6. Don't create God classes
7. Don't forget to write tests
8. Don't ignore code quality tools (PHPStan, Pint)
9. Don't hardcode configuration values
10. Don't use global state

## Conclusion

Following these coding rules ensures:
- **Maintainable Code** - Easy to understand and modify
- **Testable Architecture** - Easy to unit test and mock
- **Scalable Design** - Can grow with business requirements
- **Team Consistency** - All developers follow same patterns
- **Quality Assurance** - High code quality and reliability

Remember: **Consistency is key**. It's better to follow these rules consistently than to have perfect code in some places and inconsistent code in others.
