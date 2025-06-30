# BoxPos - Coding Rules & Architecture Guidelines

## Table of Contents
- [Quick Start](#quick-start)
- [Development Environment](#development-environment)
- [Architecture Overview](#architecture-overview)
- [Layer Responsibilities](#layer-responsibilities)
- [Repository + Builder Pattern](#repository--builder-pattern)
- [Service Layer](#service-layer)
- [Controller Guidelines](#controller-guidelines)
- [Livewire Components](#livewire-components)
- [Mandatory Logging](#mandatory-logging)
- [Code Quality Standards](#code-quality-standards)
- [Testing Guidelines](#testing-guidelines)
- [Package Structure](#package-structure)
- [Best Practices Summary](#best-practices-summary)

## Quick Start

**⚠️ IMPORTANT: Always use Laravel Sail for development consistency**

```bash
# Setup alias (one-time setup)
alias sail='./vendor/bin/sail'

# Daily commands
sail up -d                    # Start services
sail artisan migrate         # Run migrations
sail npm run dev            # Start frontend
sail test                   # Run tests
sail composer pint          # Format code
```

## Development Environment

BoxPos uses **Laravel Sail** for consistent Docker-based development across all team members.

### Quick Setup

```bash
# 1. Clone and install
git clone <repository-url> && cd BoxPos
composer install && cp .env.example .env && php artisan key:generate

# 2. Setup Sail alias (add to ~/.bashrc or ~/.zshrc)
alias sail='./vendor/bin/sail'
source ~/.bashrc  # or ~/.zshrc

# 3. Start services
sail up -d
sail artisan migrate
sail npm install && sail npm run dev
```

### Service Access
- **Application**: http://localhost
- **MySQL**: localhost:3306
- **Redis**: localhost:6379
- **Mailpit**: http://localhost:8025
- **Vite Dev Server**: http://localhost:5173

### Essential Commands

```bash
# Development
sail up -d                    # Start services
sail down                     # Stop services
sail artisan migrate         # Run migrations
sail npm run dev            # Frontend development
sail test                   # Run tests

# Code Quality
sail composer pint          # Format code
sail composer phpstan       # Static analysis

# Database
sail artisan tinker         # Database REPL
sail artisan migrate:fresh --seed

# Debugging
SAIL_XDEBUG_MODE=develop,debug sail up -d
sail logs                   # View logs
```

### ⚠️ Development Rules

**✅ ALWAYS use Sail commands:**
```bash
sail artisan migrate        # ✅ Correct
sail composer install      # ✅ Correct
sail test                  # ✅ Correct
```

**❌ NEVER run directly in local development:**
```bash
php artisan migrate        # ❌ Wrong
composer install          # ❌ Wrong
phpunit                   # ❌ Wrong
```

## Architecture Overview

BoxPos follows a **Repository + Builder Pattern** architecture with clear separation of concerns:

```
Controller → Service → Repository → Builder → Model → Database
```

### Core Principles
1. **Single Responsibility** - Each class has one clear purpose
2. **Dependency Injection** - Constructor injection for all dependencies
3. **SOLID Principles** - Follow all SOLID design principles
4. **Package-Based Modularity** - Self-contained packages with clear boundaries
5. **Mandatory Logging** - All business operations must be logged

## Layer Responsibilities

### 1. Controllers
- Handle HTTP requests/responses
- Use Form Requests for validation
- Call Service methods only
- **MUST** use `Loggable` trait
- **NEVER** access Models/Repositories directly

```php
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable; // ← MANDATORY

    public function store(StoreCustomerRequest $request)
    {
        $this->logActivity('customer_form_submitted', [
            'user_id' => auth()->id(),
        ]);

        $customer = $this->customerService->createCustomer($request->validated());
        return new CustomerResource($customer);
    }
}
```

### 2. Services
- Business logic and workflows
- Transaction management
- Event dispatching
- **MUST** use `Loggable` trait
- **NEVER** query database directly

```php
use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable; // ← MANDATORY

    public function createCustomer(array $data): Customer
    {
        $this->logActivity('customer_creation_started', [
            'user_id' => auth()->id(),
        ]);

        $customer = $this->customerRepository->create($data);
        event(new CustomerCreated($customer));

        return $customer;
    }
}
```

### 3. Repositories
- Data access abstraction
- Use Builder Pattern for complex queries
- **NEVER** contain business logic

```php
public function getVipCustomers(float $minSales, float $maxDebt): Collection
{
    return $this->model->query()
        ->vipCustomers($minSales, $maxDebt)
        ->withCreator()
        ->orderByHighestSales()
        ->get();
}
```

### 4. Builders
- Fluent query interface
- Method chaining for flexibility
- Reusable query components

```php
public function vipCustomers(float $minSales = 10000, float $maxDebt = 1000): self
{
    return $this->salesAbove($minSales)->debtBelow($maxDebt);
}

public function applyCriteria(array $criteria): self
{
    return $this
        ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
        ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']));
}
```

## Repository + Builder Pattern

### Key Rules

1. **One Repository per Model** - Each model has its own repository
2. **Builder Integration** - Use Builder Pattern for complex queries
3. **No Business Logic** - Repositories only handle data access
4. **Dependency Injection** - Inject repositories into services

### Repository Structure

```php
namespace Packages\Customer\Repositories;

class CustomerRepository
{
    protected Customer $model;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    // Basic CRUD
    public function create(array $data): Customer { }
    public function findById(int $id): ?Customer { }
    public function update(Customer $customer, array $data): Customer { }
    public function delete(Customer $customer): bool { }

    // Complex queries using Builder
    public function search(array $criteria): Collection
    {
        return $this->model->query()
            ->applyCriteria($criteria)
            ->orderByName()
            ->get();
    }
}
```

### Builder Pattern Usage

**✅ CORRECT Flow:**
```
Controller → Service → Repository → Builder → Model → Database
```

**Builder should ONLY be called in:**
- ✅ **Repositories** (primary location)
- ✅ **Livewire Components** (for UI filtering)

**Builder should NEVER be called in:**
- ❌ **Services** (use Repository methods instead)
- ❌ **Controllers** (use Service methods instead)

### Anti-Pattern: Multiple If Statements

**❌ Bad - Too many if statements:**
```php
public function search(array $criteria): Collection
{
    $query = $this->model->query();

    if (!empty($criteria['search'])) {
        $query->search($criteria['search']);
    }
    if (!empty($criteria['type'])) {
        $query->byType($criteria['type']);
    }
    // ... 8 more if statements

    return $query->get();
}
```

**✅ Good - Fluent Builder Pattern:**
```php
// Builder method
public function applyCriteria(array $criteria): self
{
    return $this
        ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
        ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']))
        ->when(!empty($criteria['group']), fn($q) => $q->byGroup($criteria['group']));
}

// Repository usage
public function search(array $criteria): Collection
{
    return $this->model->query()
        ->applyCriteria($criteria)
        ->orderByName()
        ->get();
}
```

## Service Layer

### Rules

1. **Business Logic Only** - Services contain business rules and workflows
2. **Transaction Management** - Handle database transactions in services
3. **Event Dispatching** - Dispatch domain events for important actions
4. **Repository Coordination** - Coordinate multiple repositories when needed
5. **⚠️ MANDATORY LOGGING** - All business operations MUST be logged

### Service Structure

```php
namespace Packages\Customer\Services;

use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable; // ← MANDATORY

    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function createCustomer(array $data): Customer
    {
        $this->logActivity('customer_creation_started', [
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
        ]);

        DB::beginTransaction();

        try {
            $data['customer_code'] = Customer::generateCustomerCode();
            $customer = $this->customerRepository->create($data);

            // Business logic
            event(new CustomerCreated($customer));

            DB::commit();

            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
            ]);

            return $customer;
        } catch (\Exception $e) {
            DB::rollback();

            $this->logError($e, [
                'action' => 'customer_creation',
                'user_id' => auth()->id(),
                'data' => $data,
            ]);

            throw $e;
        }
    }
}

## Controller Guidelines

### Rules

1. **Thin Controllers** - Keep controllers as thin as possible
2. **Form Requests** - Use Form Request classes for validation
3. **Resource Classes** - Use API Resource classes for JSON responses
4. **Exception Handling** - Handle exceptions gracefully with proper HTTP codes
5. **⚠️ MANDATORY LOGGING** - All controllers MUST use `Loggable` trait

### Controller Structure

```php
namespace Packages\Customer\Http\Controllers;

use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable; // ← MANDATORY

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;

        // Apply logging middleware
        $this->middleware('log.requests')->only(['store', 'update', 'destroy']);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->logActivity('customer_form_submitted', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        try {
            $customer = $this->customerService->createCustomer($request->validated());

            return $request->expectsJson()
                ? new CustomerResource($customer)
                : redirect()->route('customers.show', $customer)
                    ->with('success', 'Customer created successfully');
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'store',
                'user_id' => auth()->id(),
            ]);

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
        return Customer::query()  // ✅ OK for Livewire
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filters['type'] ?? null, fn($q) => $q->byType($this->filters['type']))
            ->when($this->filters['has_debt'] ?? false, fn($q) => $q->withDebt())
            ->orderByName()
            ->paginate(15);
    }
}
## Mandatory Logging

### ⚠️ CRITICAL: All Business Operations Must Be Logged

**This is a MANDATORY requirement for all business logic operations in BoxPos.**

### What Must Be Logged

1. **All Service Layer Operations** - Customer/Product/Order operations, payments, user auth
2. **All Controller Actions** - User activities, API requests, errors, performance metrics
3. **All Repository Operations** - Database create/update/delete operations

### Implementation Requirements

**✅ REQUIRED - All Services and Controllers MUST use Loggable trait:**

```php
use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable; // ← MANDATORY

    public function createCustomer(array $data): Customer
    {
        $this->logActivity('customer_creation_started', [
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
        ]);

        try {
            $customer = $this->customerRepository->create($data);

            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'user_id' => auth()->id(),
            ]);

            return $customer;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'customer_creation',
                'user_id' => auth()->id(),
                'data' => $data,
            ]);

            throw $e;
        }
    }
}
```

### Loggable Trait Methods

```php
// Log user activity
$this->logActivity('action_name', $data, $userId);

// Log custom events
$this->logEvent('event_name', $data);

// Log errors with context
$this->logError($exception, $context);

// Log model events (create, update, delete)
$this->logModelEvent('updated', $model, $additionalData);

// Log operation performance
$this->logOperationPerformance('operation_name', $startTime, $context);

// Log with query tracking
$result = $this->withQueryLogging('operation_name', function() {
    return $this->repository->someComplexQuery();
});
```

### Code Review Checklist

**Pull requests WILL BE REJECTED if:**
- [ ] Services missing `Loggable` trait
- [ ] Controllers missing `Loggable` trait
- [ ] Business operations not logged
- [ ] Error logging incomplete
- [ ] User context missing from logs
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
5. **Use Laravel Sail** - Always run tests through Sail

### Running Tests

```bash
# Setup alias (recommended)
alias sail='./vendor/bin/sail'

# Run all tests
sail test

# Run specific test suite
sail test tests/Unit/
sail test tests/Feature/

# Run with coverage
sail test --coverage

# Run specific test
sail test --filter CustomerServiceTest

# Run tests in parallel
sail test --parallel
```

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
│   ├── config/customer.php
│   ├── resources/views/
│   ├── routes/web.php
│   └── src/
│       ├── Builders/CustomerBuilder.php
│       ├── Http/Controllers/
│       ├── Models/Customer.php
│       ├── Repositories/CustomerRepository.php
│       ├── Services/CustomerService.php
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

### ✅ DO's

1. **Architecture**
   - Follow Repository + Builder Pattern architecture
   - Use dependency injection for all dependencies
   - Keep controllers thin and focused
   - Use Builder Pattern for complex queries

2. **Code Quality**
   - Write comprehensive tests (>80% coverage)
   - Document public methods with PHPDoc
   - Use type hints for all parameters and return types
   - Handle exceptions gracefully
   - Follow PSR standards

3. **Development**
   - **Use Laravel Sail for all local development commands**
   - Always use `sail` commands (setup alias: `alias sail='./vendor/bin/sail'`)
   - Use `when()` instead of multiple `if` statements
   - Create reusable Builder methods for common scenarios

4. **⚠️ MANDATORY Logging**
   - **Use `Loggable` trait in ALL Controllers and Services**
   - **Log ALL business operations with proper context**
   - **Log user activities for audit trails**
   - **Log errors with proper context for debugging**
   - Monitor SQL queries for performance optimization

### ❌ DON'Ts

1. **Architecture Violations**
   - Don't bypass the service layer in controllers
   - Don't put business logic in repositories
   - Don't directly query models in controllers
   - Don't ignore the Builder Pattern for complex queries

2. **Development Violations**
   - **Don't run `php artisan`, `composer`, or `npm` commands directly in local development**
   - Don't write multiple `if` statements for filtering
   - Don't duplicate query building logic
   - Don't create overly complex single methods

3. **⚠️ MANDATORY Logging Violations**
   - **NEVER create Services without `Loggable` trait**
   - **NEVER create Controllers without `Loggable` trait**
   - **NEVER skip logging for business operations**
   - **NEVER skip error logging in try-catch blocks**
   - Don't log sensitive information (passwords, tokens, personal data)

### Code Review Checklist

**Before approving any pull request, verify:**

- [ ] All Services use `Loggable` trait
- [ ] All Controllers use `Loggable` trait
- [ ] All business operations log their activities
- [ ] All errors are logged with proper context
- [ ] User IDs are included in all business operation logs
- [ ] Sensitive data is excluded from logs
- [ ] Tests are written and passing
- [ ] Documentation is updated
- [ ] Code follows Repository + Builder Pattern
- [ ] No direct database queries in Controllers/Services

### Quick Reference Commands

```bash
# Setup alias (one-time)
alias sail='./vendor/bin/sail'

# Daily development
sail up -d                    # Start services
sail artisan migrate         # Run migrations
sail npm run dev            # Start frontend
sail test                   # Run tests
sail composer pint          # Format code

# Monitoring
sail logs                   # View logs
sail artisan tinker         # Database REPL
```

## Conclusion

Following these coding rules ensures:
- **Maintainable Code** - Easy to understand and modify
- **Testable Architecture** - Easy to unit test and mock
- **Scalable Design** - Can grow with business requirements
- **Team Consistency** - All developers follow same patterns
- **Quality Assurance** - High code quality and reliability
- **Comprehensive Monitoring** - Full visibility into application behavior

**Remember: Consistency is key.** It's better to follow these rules consistently than to have perfect code in some places and inconsistent code in others.


