# Coding Rules & Architecture Guidelines

## Table of Contents
- [Local Development Environment](#local-development-environment)
- [Architecture Overview](#architecture-overview)
- [Layer Responsibilities](#layer-responsibilities)
- [Repository Pattern](#repository-pattern)
- [Builder Pattern](#builder-pattern)
- [Service Layer](#service-layer)
- [Controller Guidelines](#controller-guidelines)
- [Livewire Components](#livewire-components)
- [Logging Package](#logging-package)
- [Mandatory Logging Requirements](#mandatory-logging-requirements)
- [Code Quality Standards](#code-quality-standards)
- [Testing Guidelines](#testing-guidelines)
- [Package Structure](#package-structure)

## Local Development Environment

For local development, we use **Laravel Sail** to ensure consistent development environments across all team members.

> **⚠️ IMPORTANT:** All examples in this document show two formats:
> - **Without alias:** `./vendor/bin/sail command` (works immediately)  
> - **With alias:** `sail command` (requires one-time setup, see [Setting Up Sail Alias](#setting-up-sail-alias))

### Laravel Sail Setup

Laravel Sail provides a Docker-based development environment that includes:
- PHP 8.x with all required extensions
- MySQL/PostgreSQL database
- Redis for caching and sessions
- Node.js for frontend asset compilation
- Mailpit for email testing

### Installation & Setup

1. **Initial Setup:**
```bash
# Clone the repository
git clone <repository-url>
cd BoxPos

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

2. **Start Sail Environment:**
```bash
# Start all services (without alias)
./vendor/bin/sail up -d

# Setup alias for convenience (add to ~/.bashrc or ~/.zshrc)
alias sail='./vendor/bin/sail'

# After setting up alias, you can use short commands:
sail up -d

# Stop services (with alias)
sail down

# Restart services (with alias)  
sail restart

# Or without alias:
./vendor/bin/sail down
./vendor/bin/sail restart
```

3. **Database Setup:**
```bash
# Run migrations (without alias)
./vendor/bin/sail artisan migrate

# Seed database (if seeders exist) 
./vendor/bin/sail artisan db:seed

# OR with alias (after setup)
sail artisan migrate
sail artisan db:seed
```

### Setting Up Sail Alias

**For Bash users (~/.bashrc):**
```bash
# Add this line to ~/.bashrc
echo "alias sail='./vendor/bin/sail'" >> ~/.bashrc

# Reload your shell
source ~/.bashrc
```

**For Zsh users (~/.zshrc):**
```bash
# Add this line to ~/.zshrc  
echo "alias sail='./vendor/bin/sail'" >> ~/.zshrc

# Reload your shell
source ~/.zshrc
```

**Verify alias setup:**
```bash
# Test the alias
sail --version

# Should show Laravel Sail version instead of "command not found"
```

### Daily Development Commands

**Setup alias first (highly recommended):**
```bash
# Add this line to your shell profile (~/.bashrc, ~/.zshrc, ~/.profile)
alias sail='./vendor/bin/sail'

# Reload your shell or run:
source ~/.bashrc  # or ~/.zshrc
```

**Use Sail for all development tasks:**

```bash
# Artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan make:model Customer
./vendor/bin/sail artisan make:controller CustomerController
./vendor/bin/sail artisan queue:work

# Composer commands
./vendor/bin/sail composer install
./vendor/bin/sail composer require package/name
./vendor/bin/sail composer dump-autoload

# Node/NPM commands
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build

# Testing
./vendor/bin/sail test
./vendor/bin/sail test --filter CustomerTest

# Database commands
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan tinker

# Code quality tools
./vendor/bin/sail composer pint
./vendor/bin/sail composer phpstan
```

**After setting up the alias, you can use short commands:**

```bash
# Artisan commands (with alias)
sail artisan migrate
sail artisan make:model Customer
sail artisan make:controller CustomerController
sail artisan queue:work

# Composer commands (with alias)
sail composer install
sail composer require package/name
sail composer dump-autoload

# Node/NPM commands (with alias)
sail npm install
sail npm run dev
sail npm run build

# Testing (with alias)
sail test
sail test --filter CustomerTest

# Database commands (with alias)
sail artisan migrate:fresh --seed
sail artisan tinker

# Code quality tools (with alias)
sail composer pint
sail composer phpstan
```

**Summary of command formats:**
```bash
# Without alias (always works immediately)
./vendor/bin/sail [command]

# With alias (requires one-time setup)
sail [command]
```

**Without alias (if you haven't setup the alias):**
```bash
# You can use the full path
./vendor/bin/sail artisan migrate
./vendor/bin/sail test
./vendor/bin/sail composer install
```

**⚠️ Important:** Never run these commands directly in local development:
```bash
# ❌ DON'T do this in local development
php artisan migrate
composer install
npm run dev
phpunit
```

### Environment Configuration

**Key `.env` variables for Sail:**

```env
# Application
APP_NAME="BoxPos"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database (Sail default)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=boxpos
DB_USERNAME=sail
DB_PASSWORD=password

# Redis (Sail default)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (Mailpit for testing)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### Service Access

**Default Sail service ports:**
- **Application:** http://localhost (port 80)
- **MySQL:** localhost:3306
- **Redis:** localhost:6379
- **Mailpit:** http://localhost:8025
- **Vite Dev Server:** http://localhost:5173

### Development Workflow

1. **Start your development session:**
```bash
# Without alias
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev  # For frontend development

# With alias (after setup)
sail up -d
sail npm run dev  # For frontend development
```

2. **Make code changes using your preferred IDE**

3. **Run tests:**
```bash
# Without alias
./vendor/bin/sail test

# With alias
sail test
```

4. **Check code quality:**
```bash
# Without alias
./vendor/bin/sail composer pint      # Code formatting
./vendor/bin/sail composer phpstan   # Static analysis

# With alias
sail composer pint      # Code formatting
sail composer phpstan   # Static analysis
```

5. **Database operations:**
```bash
# Without alias
./vendor/bin/sail artisan migrate    # Run new migrations
./vendor/bin/sail artisan tinker     # Database REPL

# With alias
sail artisan migrate    # Run new migrations
sail artisan tinker     # Database REPL
```

### Debugging

**Enable Xdebug (when needed):**
```bash
# Start Sail with Xdebug (without alias)
SAIL_XDEBUG_MODE=develop,debug ./vendor/bin/sail up -d

# With alias
SAIL_XDEBUG_MODE=develop,debug sail up -d
```

**View logs:**
```bash
# Application logs (without alias)
./vendor/bin/sail logs

# Specific service logs (without alias)
./vendor/bin/sail logs mysql
./vendor/bin/sail logs redis

# With alias
sail logs
sail logs mysql
sail logs redis
```

### Team Collaboration

**Rules for team development:**

1. **Always use Sail commands** - Never run `php artisan` or `composer` directly
2. **Consistent environment** - All developers use the same Docker services
3. **Share Sail configuration** - Keep `docker-compose.yml` in version control
4. **Document custom services** - Any additional Docker services must be documented

### Performance Optimization

**For better performance:**

```bash
# Use dedicated volumes for vendor and node_modules
# (Already configured in docker-compose.yml)

# Optimize Composer (without alias)
./vendor/bin/sail composer install --optimize-autoloader --no-dev

# Clear application caches (without alias)
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache

# With alias
sail composer install --optimize-autoloader --no-dev
sail artisan optimize:clear
sail artisan config:cache
sail artisan route:cache
sail artisan view:cache
```

### Troubleshooting

**Common issues and solutions:**

```bash
# Ports already in use (without alias)
./vendor/bin/sail down
./vendor/bin/sail up -d

# Permission issues (without alias)
./vendor/bin/sail root-shell
chown -R sail:sail /var/www/html

# Database connection issues (without alias)
./vendor/bin/sail artisan config:clear
./vendor/bin/sail down && ./vendor/bin/sail up -d

# Clear all caches (without alias)
./vendor/bin/sail artisan optimize:clear

# With alias (after setup)
sail down
sail up -d
sail root-shell
sail artisan config:clear
sail down && sail up -d
sail artisan optimize:clear
```

### Production Deployment

**Note:** Laravel Sail is for **local development only**. Production deployment uses different containerization or traditional server setup.

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

### **Repository Anti-Patterns & Solutions**

**❌ Anti-Pattern: Duplicate Query Logic**
```php
// Bad - Duplicate filtering logic
public function search(array $criteria): Collection
{
    $query = $this->model->query();
    // 10 if statements...
    return $query->get();
}

public function searchWithPagination(array $criteria, int $perPage): LengthAwarePaginator
{
    $query = $this->model->query();
    // Same 10 if statements duplicated...
    return $query->paginate($perPage);
}
```

**✅ Solution: Extract Query Building**
```php
// Good - Reusable query building
public function search(array $criteria): Collection
{
    return $this->buildSearchQuery($criteria)->get();
}

public function searchWithPagination(array $criteria, int $perPage): LengthAwarePaginator
{
    return $this->buildSearchQuery($criteria)->paginate($perPage);
}

private function buildSearchQuery(array $criteria)
{
    return $this->model->query()
        ->applyCriteria($criteria)
        ->orderByName();
}
```

**❌ Anti-Pattern: Repository with Business Logic**
```php
// Bad - Business logic in repository
public function getVipCustomers(): Collection
{
    $customers = $this->model->where('total_sales', '>', 10000)->get();
    
    // Business logic doesn't belong here
    foreach ($customers as $customer) {
        $customer->sendWelcomeEmail();
        $customer->assignLoyaltyPoints(100);
    }
    
    return $customers;
}
```

**✅ Solution: Keep Repository for Data Access Only**
```php
// Good - Repository for data access only
public function getVipCustomers(float $minSales, float $maxDebt): Collection
{
    return $this->model->query()
        ->vipCustomers($minSales, $maxDebt)
        ->withCreator()
        ->orderByHighestSales()
        ->get();
}

// Business logic belongs in Service
// CustomerService::processVipCustomers() handles email and points
```

## Builder Pattern

### **⚠️ CRITICAL: Where to Use Builder Pattern**

**Builder Pattern should ONLY be called in:**
- ✅ **Repositories** - Primary location for Builder usage
- ✅ **Livewire Components** - For UI filtering and direct queries
- ✅ **Direct Model Queries** - In simple cases (not recommended)

**Builder Pattern should NEVER be called in:**
- ❌ **Services** - Services should only call Repository methods
- ❌ **Controllers** - Controllers should only call Service methods

### **Correct Flow:**
```
Controller → Service → Repository → Builder → Model → Database
```

### **Example:**

```php
// ❌ WRONG - Service using Builder directly
class CustomerService 
{
    public function getVipCustomers()
    {
        return Customer::query()  // ❌ Don't do this
            ->vipCustomers()
            ->get();
    }
}

// ✅ CORRECT - Service calls Repository
class CustomerService 
{
    public function getVipCustomers(float $minSales, float $maxDebt): Collection
    {
        return $this->customerRepository->getVipCustomers($minSales, $maxDebt);
    }
}

// ✅ CORRECT - Repository uses Builder
class CustomerRepository
{
    public function getVipCustomers(float $minSales, float $maxDebt): Collection
    {
        return $this->model->query()  // ✅ Builder called here
            ->vipCustomers($minSales, $maxDebt)
            ->withCreator()
            ->get();
    }
}
```

### **Livewire Exception:**
```php
// ✅ EXCEPTION - Livewire can use Builder directly
class CustomerManagement extends Component
{
    public function getCustomersProperty()
    {
        return Customer::query()  // ✅ OK for Livewire
            ->search($this->search)
            ->byType($this->filterType)
            ->paginate(15);
    }
}
```

### Implementation Rules

1. **Extend Eloquent Builder** - Custom builders should extend `Illuminate\Database\Eloquent\Builder`
2. **Method Chaining** - All methods should return `self` for chaining
3. **Descriptive Names** - Method names should clearly describe their purpose
4. **Reusable Logic** - Create small, composable methods
5. **Avoid Multiple If Statements** - Use fluent methods instead of conditional chains

### **⚠️ ANTI-PATTERN: Too Many If Statements**

**❌ Bad - Multiple if statements (code smell):**
```php
// Repository with too many if statements
public function search(array $criteria): Collection
{
    $query = $this->model->query();
    
    if (!empty($criteria['search'])) {
        $query->search($criteria['search']);
    }
    if (!empty($criteria['type'])) {
        $query->byType($criteria['type']);
    }
    if (!empty($criteria['group'])) {
        $query->byGroup($criteria['group']);
    }
    if (!empty($criteria['gender'])) {
        $query->byGender($criteria['gender']);
    }
    // ... 8 more if statements (code duplication)
    
    return $query->get();
}
```

**✅ Good - Fluent Builder Pattern:**
```php
// Builder with criteria application method
public function applyCriteria(array $criteria): self
{
    return $this
        ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
        ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']))
        ->when(!empty($criteria['group']), fn($q) => $q->byGroup($criteria['group']))
        ->when(!empty($criteria['gender']), fn($q) => $q->byGender($criteria['gender']))
        ->when(!empty($criteria['has_debt']), fn($q) => $q->withDebt())
        ->when(!empty($criteria['min_sales']), fn($q) => $q->salesAbove($criteria['min_sales']))
        ->when(!empty($criteria['max_debt']), fn($q) => $q->debtBelow($criteria['max_debt']))
        ->when(!empty($criteria['active_days']), fn($q) => $q->activeInLastDays($criteria['active_days']));
}

// Repository using Builder method
public function search(array $criteria): Collection
{
    return $this->model->query()
        ->applyCriteria($criteria)
        ->orderByName()
        ->get();
}
```

### **Business Scenario Methods**

Create predefined methods for common business scenarios:

```php
// Builder with business scenarios
public function forScenario(string $scenario): self
{
    return match ($scenario) {
        'marketing_campaign' => $this->activeInLastDays(60)->salesAbove(5000),
        'debt_collection' => $this->withDebt()->orderByHighestDebt(),
        'birthday_promotion' => $this->birthdayThisMonth()->orderByName(),
        'loyalty_program' => $this->salesAbove(20000)->activeInLastDays(30),
        'win_back_campaign' => $this->inactiveInLastDays(90)->salesAbove(10000),
        default => $this,
    };
}

// Usage in Repository
public function getCustomersForScenario(string $scenario): Collection
{
    return $this->model->query()
        ->forScenario($scenario)
        ->get();
}
```

### **Dynamic Filter Application**

For handling dynamic filters without repetitive code:

```php
// Builder with dynamic filter application
public function applyFilters(array $filters): self
{
    foreach ($filters as $filter => $value) {
        if (empty($value)) continue;

        match ($filter) {
            'search' => $this->search($value),
            'type' => $this->byType($value),
            'group' => $this->byGroup($value),
            'gender' => $this->byGender($value),
            'has_debt' => $value ? $this->withDebt() : $this,
            'min_sales' => $this->salesAbove($value),
            'max_debt' => $this->debtBelow($value),
            'active_days' => $this->activeInLastDays($value),
            'sales_range' => is_array($value) && count($value) === 2 
                ? $this->salesBetween($value[0], $value[1]) 
                : $this,
            default => $this,
        };
    }

    return $this;
}
```

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
5. **⚠️ MANDATORY LOGGING** - All business operations MUST be logged with appropriate context

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

## Logging Package

### Overview

The Logging Package provides comprehensive logging functionality for BoxPos with advanced features for monitoring, debugging, and audit trails. It follows the coding architecture principles and integrates seamlessly with the repository pattern.

### Features

- ✅ **User Activity Logging** - Track user actions and behaviors
- ✅ **SQL Query Logging** - Monitor database performance with query analysis
- ✅ **Request/Response Logging** - HTTP request/response tracking
- ✅ **Performance Monitoring** - Application performance metrics
- ✅ **Error Logging** - Comprehensive error tracking with context
- ✅ **Custom Events** - Log custom business events
- ✅ **N+1 Query Detection** - Automatic detection of performance issues
- ✅ **Log Rotation** - Automatic log file management

### Architecture Integration

The Log package follows our architectural guidelines:

```
Controller → Service → Repository → Builder → Model → Database
    ↓          ↓          ↓
  Logging   Logging    Logging (via middleware)
```

### Usage in Controllers

**✅ Correct Implementation:**

```php
namespace Packages\Customer\Http\Controllers;

use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
        
        // Apply logging middleware to specific actions
        $this->middleware('log.requests')->only(['store', 'update', 'destroy']);
        $this->middleware('log.sql')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $customers = $this->customerService->searchCustomers(
            $request->get('search', ''),
            $request->only(['type', 'group', 'gender']),
            $request->get('per_page', 15)
        );

        // Log user activity
        $this->logActivity('customers_viewed', [
            'search_term' => $request->get('search'),
            'filters' => $request->only(['type', 'group', 'gender']),
            'results_count' => $customers->count(),
        ]);

        return $request->expectsJson() 
            ? CustomerResource::collection($customers)
            : view('customers.index', compact('customers'));
    }

    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());

            // Log successful creation
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_type' => $customer->type,
            ]);

            return new CustomerResource($customer);

        } catch (\Exception $e) {
            // Log error with context
            $this->logError($e, [
                'action' => 'customer_creation',
                'request_data' => $request->except(['password']), // Exclude sensitive data
            ]);

            throw $e;
        }
    }
}
```

### Usage in Services

**✅ Service Layer Logging:**

```php
namespace Packages\Customer\Services;

use Packages\Log\Traits\Loggable;

class CustomerService
{
    use Loggable;

    protected CustomerRepository $customerRepository;

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

    public function exportCustomers(array $criteria): array
    {
        // Log process start
        $this->logProcessStep('customer_export', 'started', [
            'export_format' => $criteria['format'] ?? 'csv',
            'filters' => $criteria,
        ]);

        try {
            $exportData = $this->withQueryLogging('customer_export_query', function () use ($criteria) {
                return $this->customerRepository->getExportData($criteria);
            });

            $this->logProcessStep('customer_export', 'completed', [
                'records_exported' => count($exportData),
                'file_size_mb' => round(strlen(serialize($exportData)) / 1024 / 1024, 2),
            ]);

            return $exportData;

        } catch (\Exception $e) {
            $this->logProcessStep('customer_export', 'failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
```

### Loggable Trait Methods

The `Loggable` trait provides standardized logging methods:

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

### Middleware Usage

**Automatic Logging with Middleware:**

```php
// In Controller constructor
$this->middleware('log.requests');     // Log HTTP requests/responses
$this->middleware('log.sql');          // Log SQL queries
$this->middleware('log.performance');  // Log performance metrics

// In routes
Route::middleware(['log.requests', 'log.performance'])->group(function () {
    Route::apiResource('customers', CustomerController::class);
});

// Global middleware (in bootstrap/app.php)
$middleware->web(append: [
    \Packages\Log\Middleware\LogPerformance::class,
]);
```

### Configuration

**Environment Variables:**

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

### Log Files Structure

The package creates organized log files:

```
storage/logs/
├── laravel-YYYY-MM-DD.log      # Main application logs
├── sql-YYYY-MM-DD.log          # SQL queries with performance
├── performance-YYYY-MM-DD.log  # Request performance metrics
└── error-YYYY-MM-DD.log        # Error logs with context
```

### Best Practices

**✅ DO's:**

1. **Always use Loggable trait** in Controllers and Services
2. **Log user activities** for audit trails
3. **Log errors with context** for debugging
4. **Use appropriate log levels** (info, warning, error)
5. **Monitor SQL queries** for performance optimization
6. **Log process steps** for complex operations
7. **Exclude sensitive data** from logs (passwords, tokens)

**❌ DON'Ts:**

1. **Don't log sensitive information** (passwords, API keys, personal data)
2. **Don't log in loops** without pagination or limits
3. **Don't ignore log performance** - logging should be fast
4. **Don't log everything** - be selective and purposeful
5. **Don't hardcode log messages** - use configuration

### Advanced Usage Examples

**Complex Process Logging:**

```php
public function processLargeDataset(array $data): array
{
    $startTime = microtime(true);
    $processId = uniqid('process_');

    $this->logProcessStep('large_dataset_processing', 'started', [
        'process_id' => $processId,
        'total_records' => count($data),
        'memory_start' => memory_get_usage(true),
    ]);

    $results = [];
    $processed = 0;

    try {
        foreach (array_chunk($data, 100) as $chunk) {
            $chunkResults = $this->withQueryLogging("process_chunk_{$processed}", function () use ($chunk) {
                return $this->processChunk($chunk);
            });

            $results = array_merge($results, $chunkResults);
            $processed += count($chunk);

            // Log progress every 1000 records
            if ($processed % 1000 === 0) {
                $this->logProcessStep('large_dataset_processing', 'progress', [
                    'process_id' => $processId,
                    'records_processed' => $processed,
                    'memory_usage' => memory_get_usage(true),
                ]);
            }
        }

        $this->logOperationPerformance('large_dataset_processing', $startTime, [
            'process_id' => $processId,
            'total_processed' => $processed,
            'memory_peak' => memory_get_peak_usage(true),
        ]);

        return $results;

    } catch (\Exception $e) {
        $this->logError($e, [
            'process_id' => $processId,
            'records_processed' => $processed,
            'action' => 'large_dataset_processing',
        ]);

        throw $e;
    }
}
```

**Monitoring Integration:**

```php
// Custom log channels for different environments
'channels' => [
    'slack_critical' => [
        'driver' => 'custom',
        'via' => Packages\Log\Channels\SlackChannel::class,
        'webhook' => env('SLACK_WEBHOOK_URL'),
        'level' => 'critical',
    ],
    
    'remote_file' => [
        'driver' => 'custom',
        'via' => Packages\Log\Channels\RemoteFileChannel::class,
        'path' => env('REMOTE_LOG_PATH'),
        'level' => 'debug',
    ],
],
```

### Testing with Logs

**Test log generation:**

```bash
# Without alias
./vendor/bin/sail artisan log:cleanup --days=30
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log

# With alias
sail artisan log:cleanup --days=30
sail exec laravel.test tail -f /var/www/html/storage/logs/sql-$(date +%Y-%m-%d).log
```

### Log Package Rules

1. **Follow Architecture** - Use through Services and Controllers only
2. **Use Middleware** - Apply logging middleware appropriately  
3. **Context is King** - Always provide relevant context
4. **Performance Aware** - Don't impact application performance
5. **Security First** - Never log sensitive information
6. **Structured Data** - Use arrays for structured logging
7. **Consistent Format** - Follow logging standards across the application

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
5. **Use Laravel Sail** - Always run tests through Sail in local development using `./vendor/bin/sail` or setup alias

### Running Tests with Laravel Sail

**Setup Sail alias (recommended):**
```bash
# Add to your shell profile (~/.bashrc, ~/.zshrc, etc.)
alias sail='./vendor/bin/sail'

# Or use it directly without alias
./vendor/bin/sail
```

**Basic test commands:**
```bash
# Run all tests (without alias)
./vendor/bin/sail test

# Run specific test file (without alias)
./vendor/bin/sail test tests/Unit/Packages/Customer/Services/CustomerServiceTest.php

# Run tests with coverage (without alias)
./vendor/bin/sail test --coverage

# Run tests with filter (without alias)
./vendor/bin/sail test --filter test_creates_customer_successfully

# Run feature tests only (without alias)
./vendor/bin/sail test tests/Feature/

# Run unit tests only (without alias)
./vendor/bin/sail test tests/Unit/

# Run tests in parallel (without alias)
./vendor/bin/sail test --parallel

# Run tests with verbose output (without alias)
./vendor/bin/sail test --verbose

# With alias (after setup)
sail test
sail test tests/Unit/Packages/Customer/Services/CustomerServiceTest.php
sail test --coverage
sail test --filter test_creates_customer_successfully
sail test tests/Feature/
sail test tests/Unit/
sail test --parallel
sail test --verbose
```

**Test database setup:**
```bash
# Create test database (without alias)
./vendor/bin/sail artisan migrate --env=testing

# Refresh test database (without alias)
./vendor/bin/sail artisan migrate:fresh --env=testing

# Seed test database (without alias)
./vendor/bin/sail artisan db:seed --env=testing

# With alias (after setup)
sail artisan migrate --env=testing
sail artisan migrate:fresh --env=testing
sail artisan db:seed --env=testing
```

**Important Notes:**
- **Never run `php artisan test` or `phpunit` directly** in local development
- **Always use `./vendor/bin/sail test` or `sail test` (with alias)**
- **Setup the alias once** to avoid typing the full path repeatedly
- **All team members must use the same approach** for consistency

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
├── Log/
│   ├── config/
│   │   └── logging.php
│   ├── examples/
│   │   ├── CustomerController_example.php
│   │   └── CustomerService_example.php
│   └── src/
│       ├── Channels/
│       │   ├── SlackChannel.php
│       │   └── RemoteFileChannel.php
│       ├── Console/
│       │   └── Commands/
│       │       └── CleanupLogsCommand.php
│       ├── Middleware/
│       │   ├── LogRequests.php
│       │   ├── LogSqlQueries.php
│       │   └── LogPerformance.php
│       ├── Services/
│       │   ├── LogService.php
│       │   ├── LogFormatterService.php
│       │   └── QueryPerformanceService.php
│       ├── Traits/
│       │   ├── Loggable.php
│       │   └── LogsQueries.php
│       ├── Exceptions/
│       │   └── LoggingException.php
│       └── LogServiceProvider.php
```

### Package Rules

1. **Self-Contained** - Each package should be self-contained
2. **Service Provider** - Each package must have a service provider
3. **Configuration** - Package-specific configuration files
4. **Migrations** - Database migrations within packages
5. **Tests** - Package-specific tests

## Mandatory Logging Requirements

### ⚠️ CRITICAL: All Business Operations Must Be Logged

**This is a MANDATORY requirement for all business logic operations in BoxPos.**

### What Must Be Logged

1. **All Service Layer Operations**
   - Customer creation, updates, deletions
   - Product operations (create, update, delete, inventory changes)
   - Order processing and status changes
   - Payment processing and refunds
   - User authentication and authorization
   - Data imports and exports
   - System configuration changes

2. **All Controller Actions**
   - User activities (page views, form submissions)
   - API requests and responses
   - Error conditions and exceptions
   - Performance metrics for slow operations

3. **All Repository Operations That Modify Data**
   - Database create, update, delete operations
   - Bulk operations and data migrations
   - File system operations

### Enforcement Rules

**✅ REQUIRED Implementation:**

```php
// All Services MUST use Loggable trait
class CustomerService
{
    use Loggable; // ← MANDATORY

    public function createCustomer(array $data): Customer
    {
        // Log operation start
        $this->logActivity('customer_creation_started', [
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
        ]);

        try {
            $customer = $this->customerRepository->create($data);
            
            // Log successful operation
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'user_id' => auth()->id(),
            ]);

            return $customer;
        } catch (\Exception $e) {
            // Log error - MANDATORY
            $this->logError($e, [
                'action' => 'customer_creation',
                'user_id' => auth()->id(),
                'data' => $data,
            ]);
            
            throw $e;
        }
    }
}

// All Controllers MUST use Loggable trait
class CustomerController extends Controller
{
    use Loggable; // ← MANDATORY

    public function store(StoreCustomerRequest $request)
    {
        // Log user action - MANDATORY
        $this->logActivity('customer_form_submitted', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $customer = $this->customerService->createCustomer($request->validated());
            
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            // Log controller error - MANDATORY
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'store',
                'user_id' => auth()->id(),
            ]);
            
            throw $e;
        }
    }
}
```

### Code Review Checklist

**Before approving any pull request, verify:**

- [ ] All Services use `Loggable` trait
- [ ] All Controllers use `Loggable` trait
- [ ] All business operations log their activities
- [ ] All errors are logged with proper context
- [ ] User IDs are included in all business operation logs
- [ ] Sensitive data is excluded from logs
- [ ] Appropriate log levels are used
- [ ] Performance-sensitive operations include timing logs

### Violation Consequences

**Pull requests WILL BE REJECTED if:**
- Business operations are not logged
- Loggable trait is missing from Services or Controllers
- Error logging is incomplete or missing
- User context is missing from business operation logs

### Performance Requirements

**All logged operations must:**
- Include user context (user_id, action, timestamp)
- Complete logging in under 10ms
- Use appropriate log levels
- Include relevant business context
- Exclude sensitive data (passwords, tokens, personal data)

### Monitoring and Compliance

**Daily monitoring requirements:**
```bash
# Check for missing logging in new code
sail artisan log:check-compliance

# Monitor log coverage
sail artisan log:coverage-report

# Check for performance issues
sail artisan log:performance-check
```

**Weekly compliance review:**
- Review all new Services and Controllers for logging compliance
- Check log coverage reports
- Verify error logging completeness
- Review performance impact of logging

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
11. **Use `when()` instead of multiple `if` statements**
12. **Create reusable Builder methods for common scenarios**
13. **Extract duplicate query logic into private methods**
14. **Use business scenario methods (`forScenario()`)**
15. **Apply filters dynamically with `applyFilters()`**
16. **Use Laravel Sail for all local development commands**
17. **Always use `./vendor/bin/sail` or setup alias for Sail commands**
18. **⚠️ MANDATORY: Use Loggable trait in all Controllers and Services**
19. **⚠️ MANDATORY: Log all business operations with proper context**
20. **⚠️ MANDATORY: Log user activities for audit trails**
21. **⚠️ MANDATORY: Log errors with proper context for debugging**
22. **Use appropriate log levels (info, warning, error)**
23. **Monitor SQL queries for performance optimization**

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
11. **Don't write multiple `if` statements for filtering**
12. **Don't duplicate query building logic**
13. **Don't mix data access with business logic**
14. **Don't create overly complex single methods**
15. **Don't ignore the Builder Pattern for complex queries**
16. **Don't run `php artisan`, `composer`, or `npm` commands directly in local development**
17. **Don't use Sail for production deployment**
18. **⚠️ NEVER create Services without Loggable trait**
19. **⚠️ NEVER create Controllers without Loggable trait**
20. **⚠️ NEVER skip logging for business operations**
21. **⚠️ NEVER skip error logging in try-catch blocks**
22. **Don't log sensitive information (passwords, tokens, personal data)**
23. **Don't log in loops without pagination or limits**
24. **Don't ignore log performance - logging should be fast**
25. **Don't log everything - be selective and purposeful**

## Builder Pattern Best Practices

### **Code Smell Indicators:**

1. **Multiple consecutive `if` statements** - Use `when()` or `applyCriteria()`
2. **Duplicate query logic** - Extract to private methods
3. **Long parameter lists** - Use arrays or DTOs
4. **Complex conditional logic** - Use `match()` or scenario methods
5. **Mixed concerns** - Separate data access from business logic

### **Refactoring Guidelines:**

```php
// 🔄 REFACTOR THIS:
if ($criteria['search']) $query->search($criteria['search']);
if ($criteria['type']) $query->byType($criteria['type']);
if ($criteria['group']) $query->byGroup($criteria['group']);
// ... 8 more if statements

// 🎯 TO THIS:
$query->applyCriteria($criteria);

// 🔄 REFACTOR THIS:
public function getMarketingCustomers() { /* complex logic */ }
public function getDebtCollectionCustomers() { /* similar logic */ }
public function getBirthdayCustomers() { /* similar logic */ }

// 🎯 TO THIS:
public function getCustomersForScenario(string $scenario) {
    return $this->model->query()->forScenario($scenario)->get();
}
```

## Conclusion

Following these coding rules ensures:
- **Maintainable Code** - Easy to understand and modify
- **Testable Architecture** - Easy to unit test and mock
- **Scalable Design** - Can grow with business requirements
- **Team Consistency** - All developers follow same patterns
- **Quality Assurance** - High code quality and reliability
- **Comprehensive Monitoring** - Full visibility into application behavior through logging

### **Code Quality Metrics**

Monitor these metrics to ensure code quality:

1. **Cyclomatic Complexity** - Keep methods under 10 complexity points
2. **Method Length** - Maximum 20 lines per method
3. **Class Length** - Maximum 200 lines per class
4. **Parameter Count** - Maximum 4 parameters per method
5. **If Statement Count** - Maximum 3 consecutive if statements
6. **Duplication** - Zero tolerance for duplicate code blocks
7. **Test Coverage** - Minimum 80% code coverage
8. **Documentation** - 100% public method documentation
9. **⚠️ MANDATORY: Log Coverage** - 100% of business operations must have logging
10. **⚠️ MANDATORY: Error Log Coverage** - 100% of try-catch blocks must log errors
11. **⚠️ MANDATORY: Loggable Trait Usage** - 100% of Services and Controllers must use Loggable trait
12. **Performance Monitoring** - SQL queries and request performance tracked

### **Builder Pattern Checklist**

Before committing, ensure your Builder Pattern implementation:

- ✅ Uses fluent interface with method chaining
- ✅ Avoids multiple consecutive if statements
- ✅ Has reusable criteria application methods
- ✅ Includes business scenario methods
- ✅ Separates data access from business logic
- ✅ Has proper type hints and documentation
- ✅ Follows single responsibility principle
- ✅ Is covered by unit tests

### **⚠️ MANDATORY Logging Package Checklist**

**🚫 PULL REQUESTS WILL BE REJECTED WITHOUT:**

- ✅ **MANDATORY:** Uses `Loggable` trait in ALL Controllers and Services
- ✅ **MANDATORY:** Logs ALL business operations and user activities  
- ✅ **MANDATORY:** Logs ALL errors with proper context and stack traces
- ✅ **MANDATORY:** Includes user_id in all business operation logs
- ✅ **MANDATORY:** Uses appropriate log levels (info, warning, error, debug)
- ✅ **MANDATORY:** Excludes sensitive data from logs (passwords, tokens)
- ✅ **MANDATORY:** Includes relevant context for debugging (user_id, request_id, etc.)
- ✅ Uses performance logging for slow operations (>1 second)
- ✅ Applies logging middleware where appropriate
- ✅ Logs process steps for complex operations
- ✅ Has proper log retention and cleanup policies
- ✅ Follows structured logging format
- ✅ Does not impact application performance significantly (logging <10ms)

### **Daily Development Logging Workflow**

**Monitor logs during development:**

```bash
# Without alias - Monitor application logs
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log

# Without alias - Monitor SQL performance
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/sql-$(date +%Y-%m-%d).log

# Without alias - Monitor performance metrics
./vendor/bin/sail exec laravel.test tail -f /var/www/html/storage/logs/performance-$(date +%Y-%m-%d).log

# With alias (after setup)
sail exec laravel.test tail -f /var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log
sail exec laravel.test tail -f /var/www/html/storage/logs/sql-$(date +%Y-%m-%d).log
sail exec laravel.test tail -f /var/www/html/storage/logs/performance-$(date +%Y-%m-%d).log

# Clean up old logs
./vendor/bin/sail artisan log:cleanup --days=30
# With alias
sail artisan log:cleanup --days=30
```

Remember: **Consistency is key**. It's better to follow these rules consistently than to have perfect code in some places and inconsistent code in others.
