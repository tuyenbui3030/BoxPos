# BoxPos Database Seeders

## Overview

The BoxPos seeding system is designed to support multiple environments with different data requirements:

- **Development**: Rich data for demonstration and manual testing
- **Testing**: Minimal data for automated tests
- **Production**: Essential system data only

## Environment Configuration

### Configuration File

All seeding configurations are defined in `config/seeding.php`. This file contains:

- Environment-specific record counts
- Template data for consistent seeding
- Execution order for seeders
- Validation rules

### Environment Detection

The system automatically detects the current environment:

- `local` or `development` → Development mode (rich data)
- `testing` → Testing mode (minimal data)
- `production` → Production mode (essential data only)

## Usage

### Testing Environment Configuration

```bash
# Test current environment settings
php artisan seeding:test-environment

# Test specific environment
php artisan seeding:test-environment --environment=testing
php artisan seeding:test-environment --environment=production
```

### Running Seeders

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=Database\\Seeders\\UserSeeder

# Run with specific environment (for testing)
APP_ENV=testing php artisan db:seed
```

## BasePackageSeeder Features

### Environment-Aware Methods

```php
// Get record count based on environment
$count = $this->getRecordCount(100, 10); // 100 for dev, 10 for testing

// Get configuration value
$value = $this->getConfigValue('customers_per_store', 50);

// Check environment
if ($this->isDevelopmentEnvironment()) {
    // Create rich demo data
}

if ($this->isTestingEnvironment()) {
    // Create minimal test data
}
```

### Store Distribution

```php
// Distribute records across stores
$this->seedForAllStores(function ($store) {
    Customer::factory()
        ->count($this->getConfigRecordCount('customers_per_store'))
        ->for($store)
        ->create();
});
```

### Template Data

```php
// Get template data
$categories = $this->getTemplateDataFromConfig('material_categories');
$randomCategories = $this->getRandomTemplateItemsFromConfig('material_categories', 5);
```

### Transaction Management

```php
public function run(): void
{
    $this->executeEnvironmentAwareSeeding(function () {
        // Your seeding logic here
        $this->seedData();
    });
}
```

## Helper Classes

### SeedingConfigHelper

Static helper for accessing configuration:

```php
use Database\Seeders\Helpers\SeedingConfigHelper;

// Get current environment
$env = SeedingConfigHelper::getCurrentEnvironment();

// Get configuration value
$value = SeedingConfigHelper::get('customers_per_store', 50);

// Get template data
$categories = SeedingConfigHelper::getTemplate('material_categories');

// Check environment capabilities
if (SeedingConfigHelper::shouldCreateRichData()) {
    // Create demonstration data
}
```

### EnvironmentAwareSeeding Trait

Provides environment-specific methods:

```php
use Database\Seeders\Traits\EnvironmentAwareSeeding;

class MySeeder extends BasePackageSeeder
{
    use EnvironmentAwareSeeding;

    public function run(): void
    {
        $count = $this->getEnvironmentRecordCount('my_records', 100, 10);
        $batchSize = $this->getEnvironmentBatchSize();
        
        if ($this->shouldSeedRichData()) {
            // Create rich data for development
        }
    }
}
```

## Configuration Structure

### Environment Sections

```php
'development' => [
    'stores_count' => 3,
    'customers_per_store' => 50,
    'batch_size' => 100,
    // ... more configuration
],

'testing' => [
    'stores_count' => 2,
    'customers_per_store' => 5,
    'batch_size' => 50,
    // ... minimal configuration
],

'production' => [
    'stores_count' => 1,
    'customers_per_store' => 0, // No sample customers
    'batch_size' => 25,
    // ... essential data only
]
```

### Template Data

```php
'templates' => [
    'material_categories' => [
        'Xi măng', 'Sắt thép', 'Gạch', 'Cát', 'Đá', // ...
    ],
    'departments' => [
        'Bán hàng', 'Kho', 'Kế toán', 'Quản lý', // ...
    ],
    // ... more templates
]
```

### Validation Rules

```php
'validation' => [
    'required_stores_minimum' => 1,
    'required_admin_users_minimum' => 1,
    'max_records_per_batch' => 1000,
    // ... more validation rules
]
```

## Best Practices

1. **Always use BasePackageSeeder** as the parent class for package seeders
2. **Use configuration values** instead of hardcoded numbers
3. **Implement store isolation** for business data
4. **Use transactions** for data consistency
5. **Log seeding activities** for debugging
6. **Validate requirements** before seeding
7. **Use template data** for consistent reference data

## Example Seeder

```php
<?php

namespace Packages\Customer\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Customer\Models\Customer;

class CustomerSeeder extends BasePackageSeeder
{
    public function run(): void
    {
        $this->executeEnvironmentAwareSeeding(function () {
            $this->seedCustomers();
        });
    }

    private function seedCustomers(): void
    {
        $this->seedForAllStores(function ($store) {
            $count = $this->getConfigRecordCount('customers_per_store', 50, 5);
            
            Customer::factory()
                ->count($count)
                ->for($store)
                ->create();
                
            $this->logSeedingProgress('customers_seeded', [
                'store_id' => $store->id,
                'count' => $count
            ]);
        });
    }
}
```

## Troubleshooting

### Common Issues

1. **No stores found**: Run `StoreSeeder` first
2. **Configuration not found**: Check `config/seeding.php` exists
3. **Environment not detected**: Verify `APP_ENV` setting
4. **Seeding fails**: Check logs for detailed error messages

### Debugging

```bash
# Enable verbose logging
APP_DEBUG=true php artisan db:seed

# Test specific environment
php artisan seeding:test-environment --environment=testing

# Run single seeder for testing
php artisan db:seed --class=Database\\Seeders\\TestEnvironmentSeeder
```