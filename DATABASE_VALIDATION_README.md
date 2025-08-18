# Database Schema Documentation and Validation Tools

This document describes the database documentation and validation tools created for the BoxPos system.

## Overview

The BoxPos database validation suite provides comprehensive tools to:
- Generate database documentation from existing migrations
- Validate schema integrity and constraints
- Test database seeder compatibility
- Ensure multi-store architecture compliance

## Tools Available

### 1. Database Documentation Generator

**Command**: `php artisan db:generate-docs`

Generates comprehensive database documentation from existing migrations and schema.

**Options**:
- `--output=filename.md` - Specify output file (default: database-documentation.md)

**Example**:
```bash
php artisan db:generate-docs --output=schema-docs.md
```

**Output includes**:
- Table structure with column details
- Foreign key relationships
- Index information
- Multi-store architecture documentation
- Entity relationship diagrams (Mermaid format)

### 2. Schema Validation Tool

**Command**: `php artisan db:validate-schema`

Validates database schema integrity and identifies potential issues.

**Options**:
- `--fix` - Attempt to automatically fix validation issues

**Example**:
```bash
php artisan db:validate-schema --fix
```

**Validation checks**:
- Multi-store architecture compliance
- Foreign key constraint validation
- Required index verification
- Data integrity checks
- Business rule validation
- Orphaned record detection

### 3. Database Seeder Compatibility Tests

**Test File**: `tests/Feature/DatabaseSeederCompatibilityTest.php`

Comprehensive test suite to ensure database seeders work correctly.

**Run tests**:
```bash
php artisan test tests/Feature/DatabaseSeederCompatibilityTest.php
```

**Test coverage**:
- Seeder execution without errors
- Referential integrity maintenance
- Multi-store data isolation
- Enum value validation
- Unique constraint compliance
- Required field validation
- Timestamp validation
- Seeder idempotency

### 4. Database Schema Structure Tests

**Test File**: `tests/Feature/DatabaseSchemaValidationTest.php`

Tests to validate database schema structure and compliance.

**Run tests**:
```bash
php artisan test tests/Feature/DatabaseSchemaValidationTest.php
```

**Test coverage**:
- Core table existence
- Multi-store architecture structure
- Foreign key relationships
- Required indexes
- Enum column constraints
- Timestamp columns
- Unique constraints
- JSON column validation

### 5. Comprehensive Validation Script

**Script**: `scripts/validate-database.php`

Runs all validation tools in sequence for complete database health check.

**Usage**:
```bash
php scripts/validate-database.php
```

**Includes**:
- Documentation generation
- Schema validation
- Seeder compatibility tests
- Schema structure tests
- Comprehensive summary report

## Multi-Store Architecture Validation

The validation tools pay special attention to BoxPos's multi-store architecture:

### Store Isolation Checks
- Verifies `store_id` columns exist on business tables
- Validates foreign key constraints to stores table
- Checks for orphaned records without valid store references

### User-Store Relationship Validation
- Validates `user_stores` table structure
- Checks role assignments and permissions
- Verifies user-store relationship integrity

### Current Store Context
- Validates `current_store_id` on users table
- Ensures proper store context switching

## Business Rule Validation

### Enum Value Validation
- Store status: `active`, `inactive`, `suspended`
- User roles: `admin`, `manager`, `staff`, `viewer`
- Customer types: `individual`, `company`

### Required Field Validation
- User: `name`, `email`
- Store: `name`, `slug`
- Customer: `customer_code`, `customer_name`

### Unique Constraint Validation
- User emails
- Store slugs and domains
- Customer codes

## Usage Examples

### Generate Documentation
```bash
# Generate standard documentation
php artisan db:generate-docs

# Generate with custom filename
php artisan db:generate-docs --output=my-schema-docs.md
```

### Validate Schema
```bash
# Run validation checks
php artisan db:validate-schema

# Run validation and attempt fixes
php artisan db:validate-schema --fix
```

### Run Tests
```bash
# Run seeder compatibility tests
php artisan test tests/Feature/DatabaseSeederCompatibilityTest.php

# Run schema structure tests
php artisan test tests/Feature/DatabaseSchemaValidationTest.php

# Run all database tests
php artisan test --group=database
```

### Complete Validation
```bash
# Run comprehensive validation
php scripts/validate-database.php
```

## Integration with CI/CD

Add these commands to your CI/CD pipeline:

```yaml
# Example GitHub Actions step
- name: Validate Database Schema
  run: |
    php artisan migrate:fresh
    php artisan db:validate-schema
    php artisan test tests/Feature/DatabaseSeederCompatibilityTest.php
    php artisan test tests/Feature/DatabaseSchemaValidationTest.php
```

## Troubleshooting

### Common Issues

1. **Missing Tables**: Ensure migrations have been run
   ```bash
   php artisan migrate:fresh
   ```

2. **Seeder Failures**: Check for missing foreign key relationships
   ```bash
   php artisan db:validate-schema
   ```

3. **Test Failures**: Verify database connection and permissions
   ```bash
   php artisan config:cache
   php artisan config:clear
   ```

### Error Resolution

The validation tools provide detailed error messages with suggestions for resolution:

- **Foreign Key Errors**: Check migration order and constraint definitions
- **Orphaned Records**: Use `--fix` option or manually clean data
- **Index Issues**: Review migration files for missing index definitions
- **Enum Violations**: Check seeder data for invalid enum values

## Maintenance

### Regular Validation Schedule

Recommended schedule for running validations:

- **Daily**: Schema validation (`db:validate-schema`)
- **Weekly**: Full test suite
- **Before Deployment**: Complete validation script
- **After Schema Changes**: Documentation regeneration

### Updating Validation Rules

When adding new tables or business rules:

1. Update validation rules in `ValidateDatabaseSchema.php`
2. Add new test cases to test files
3. Update documentation generator patterns
4. Regenerate documentation

## Requirements Compliance

This implementation satisfies the following requirements:

- **Requirement 1.1**: Comprehensive database documentation from migrations ✅
- **Requirement 1.2**: Schema validation scripts for data integrity ✅  
- **Requirement 1.3**: Database seeder compatibility tests ✅

The tools ensure the BoxPos database maintains integrity, follows multi-store architecture principles, and supports all business requirements effectively.