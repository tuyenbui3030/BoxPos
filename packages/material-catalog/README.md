# Material Catalog Package

## Overview

The **Material Catalog** package is the core domain for building materials management in BoxPos. It provides comprehensive catalog functionality for construction materials, including product management, categorization, and technical specifications.

## Features

### Core Functionality
- **Material Management**: Complete product catalog with detailed information
- **Category Hierarchy**: Organize materials in hierarchical categories
- **Unit Management**: Support for various measurement units with conversion
- **Technical Specifications**: Store detailed technical specs for materials
- **Quality Standards**: Track quality certifications and standards
- **Image Gallery**: Multiple images per material
- **Barcode Support**: Generate and manage barcodes

### Domain Responsibilities
This package handles the **catalog domain** only:
- ✅ Material information and specifications
- ✅ Category management and hierarchy
- ✅ Unit definitions and conversions
- ✅ Technical specifications
- ❌ Inventory management (handled by `material-inventory` package)
- ❌ Pricing (handled by `material-pricing` package)
- ❌ Supplier information (handled by `material-suppliers` package)
- ❌ Purchase orders (handled by `material-purchasing` package)

## Installation

This package is automatically loaded as part of the BoxPos ecosystem.

## Database Structure

### Core Tables
- `material_units` - Units of measurement (kg, m³, pieces, etc.)
- `material_categories` - Hierarchical material categories
- `building_materials` - Main materials catalog
- `material_specifications` - Technical specifications for materials

## Usage

### Basic Material Management

```php
use Packages\MaterialCatalog\Services\BuildingMaterialService;

$materialService = app(BuildingMaterialService::class);

// Create a new material
$material = $materialService->createMaterial([
    'name' => 'Portland Cement Type I',
    'category_id' => 1,
    'primary_unit_id' => 2,
    'technical_specs' => [
        'strength' => '42.5 MPa',
        'standard' => 'TCVN 2682:2020'
    ]
]);
```

### Category Management

```php
use Packages\MaterialCatalog\Services\MaterialCategoryService;

$categoryService = app(MaterialCategoryService::class);

// Create category hierarchy
$parentCategory = $categoryService->createCategory([
    'name' => 'Xi măng',
    'code' => 'cement'
]);

$subCategory = $categoryService->createCategory([
    'name' => 'Xi măng Portland',
    'code' => 'cement_portland',
    'parent_id' => $parentCategory->id
]);
```

### Unit Management

```php
use Packages\MaterialCatalog\Services\MaterialUnitService;

$unitService = app(MaterialUnitService::class);

// Create units with conversion
$kgUnit = $unitService->createUnit([
    'code' => 'kg',
    'name' => 'Kilogram',
    'type' => 'weight',
    'conversion_factor' => 1.0
]);

$tonUnit = $unitService->createUnit([
    'code' => 'ton',
    'name' => 'Tấn',
    'type' => 'weight',
    'conversion_factor' => 1000.0,
    'base_unit_code' => 'kg'
]);
```

## Architecture

This package follows the BoxPos architecture guidelines:
- **Repository Pattern**: Data access abstraction
- **Builder Pattern**: Fluent query interface
- **Service Layer**: Business logic encapsulation
- **Domain Separation**: Clear boundaries with other packages

## Dependencies

- **Core Laravel**: Framework dependencies
- **Store Package**: For multi-tenant store support
- **User Package**: For user relationships
- **No other material packages**: This is the core domain

## Testing

```bash
sail test packages/material-catalog/src/Tests/
```

## Package Relationships

```
material-catalog (CORE)
    ↑
    ├── material-inventory (depends on catalog)
    ├── material-pricing (depends on catalog)
    └── material-purchasing (depends on catalog + suppliers)
```

## Contributing

Please follow the BoxPos coding standards and maintain the domain boundaries when contributing to this package.
