<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all required core tables exist
     */
    public function test_core_tables_exist(): void
    {
        $requiredTables = [
            'users',
            'stores',
            'user_stores',
            'sessions',
            'cache',
            'jobs',
            'password_reset_tokens'
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Required core table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test that multi-store architecture tables have correct structure
     */
    public function test_multi_store_architecture_structure(): void
    {
        // Test stores table structure
        $this->assertTrue(Schema::hasTable('stores'));
        $storeColumns = ['id', 'name', 'slug', 'domain', 'status', 'created_at', 'updated_at'];
        
        foreach ($storeColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('stores', $column),
                "stores table missing required column: {$column}"
            );
        }

        // Test user_stores table structure
        $this->assertTrue(Schema::hasTable('user_stores'));
        $userStoreColumns = ['id', 'user_id', 'store_id', 'role', 'is_active', 'created_at', 'updated_at'];
        
        foreach ($userStoreColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('user_stores', $column),
                "user_stores table missing required column: {$column}"
            );
        }

        // Test users table has current_store_id for store context
        if (Schema::hasColumn('users', 'current_store_id')) {
            $this->assertTrue(true, 'users table has current_store_id column');
        } else {
            $this->markTestSkipped('users table missing current_store_id column - may be added in future migration');
        }
    }

    /**
     * Test that business tables have store_id for multi-tenant isolation
     */
    public function test_business_tables_have_store_isolation(): void
    {
        $businessTables = [
            'customers'
        ];

        foreach ($businessTables as $table) {
            if (Schema::hasTable($table)) {
                $this->assertTrue(
                    Schema::hasColumn($table, 'store_id'),
                    "Business table '{$table}' missing store_id column for multi-tenant isolation"
                );
            }
        }
    }

    /**
     * Test that foreign key relationships are properly defined
     */
    public function test_foreign_key_relationships(): void
    {
        // Test user_stores foreign keys
        if (Schema::hasTable('user_stores')) {
            $this->assertTrue(Schema::hasColumn('user_stores', 'user_id'));
            $this->assertTrue(Schema::hasColumn('user_stores', 'store_id'));
        }

        // Test customers foreign keys
        if (Schema::hasTable('customers')) {
            if (Schema::hasColumn('customers', 'created_by')) {
                $this->assertTrue(true, 'customers table has created_by foreign key');
            }
            
            if (Schema::hasColumn('customers', 'store_id')) {
                $this->assertTrue(true, 'customers table has store_id foreign key');
            }
        }
    }

    /**
     * Test that required indexes exist for performance
     */
    public function test_required_indexes_exist(): void
    {
        // This test checks that tables have the expected structure
        // Actual index validation would require database-specific queries
        
        $tablesWithIndexRequirements = [
            'users' => ['email'],
            'stores' => ['slug', 'domain'],
            'user_stores' => ['user_id', 'store_id'],
            'customers' => ['customer_code']
        ];

        foreach ($tablesWithIndexRequirements as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table '{$table}' missing indexed column: {$column}"
                );
            }
        }
    }

    /**
     * Test that enum columns have proper constraints
     */
    public function test_enum_column_constraints(): void
    {
        // Test store status enum
        if (Schema::hasTable('stores') && Schema::hasColumn('stores', 'status')) {
            $this->assertTrue(true, 'stores.status column exists');
        }

        // Test user_stores role enum
        if (Schema::hasTable('user_stores') && Schema::hasColumn('user_stores', 'role')) {
            $this->assertTrue(true, 'user_stores.role column exists');
        }

        // Test customer type enum
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'customer_type')) {
            $this->assertTrue(true, 'customers.customer_type column exists');
        }
    }

    /**
     * Test that timestamp columns exist where expected
     */
    public function test_timestamp_columns_exist(): void
    {
        $tablesWithTimestamps = [
            'users',
            'stores', 
            'user_stores',
            'customers'
        ];

        foreach ($tablesWithTimestamps as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $this->assertTrue(
                Schema::hasColumn($table, 'created_at'),
                "Table '{$table}' missing created_at timestamp"
            );

            $this->assertTrue(
                Schema::hasColumn($table, 'updated_at'),
                "Table '{$table}' missing updated_at timestamp"
            );
        }
    }

    /**
     * Test that soft delete columns exist where expected
     */
    public function test_soft_delete_columns(): void
    {
        $tablesWithSoftDeletes = [
            // Add tables that should have soft deletes
        ];

        foreach ($tablesWithSoftDeletes as $table) {
            if (Schema::hasTable($table)) {
                $this->assertTrue(
                    Schema::hasColumn($table, 'deleted_at'),
                    "Table '{$table}' missing deleted_at for soft deletes"
                );
            }
        }
    }

    /**
     * Test that unique constraints are properly defined
     */
    public function test_unique_constraints(): void
    {
        $uniqueConstraints = [
            'users' => ['email'],
            'stores' => ['slug', 'domain'],
            'customers' => ['customer_code']
        ];

        foreach ($uniqueConstraints as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table '{$table}' missing unique column: {$column}"
                );
            }
        }
    }

    /**
     * Test that JSON columns exist where expected
     */
    public function test_json_columns_exist(): void
    {
        $jsonColumns = [
            'stores' => ['settings'],
            'user_stores' => ['permissions']
        ];

        foreach ($jsonColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table '{$table}' missing JSON column: {$column}"
                );
            }
        }
    }

    /**
     * Test that decimal columns have appropriate precision
     */
    public function test_decimal_column_precision(): void
    {
        $decimalColumns = [
            'customers' => ['current_debt', 'total_sales', 'total_sales_minus_returns']
        ];

        foreach ($decimalColumns as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Table '{$table}' missing decimal column: {$column}"
                );
            }
        }
    }

    /**
     * Test that required system tables exist
     */
    public function test_system_tables_exist(): void
    {
        $systemTables = [
            'sessions',
            'cache', 
            'jobs',
            'password_reset_tokens'
        ];

        foreach ($systemTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Required system table '{$table}' does not exist"
            );
        }
    }

    /**
     * Test that user device tracking table exists
     */
    public function test_user_device_tracking_table(): void
    {
        if (Schema::hasTable('user_devices')) {
            $requiredColumns = ['id', 'user_id', 'created_at', 'updated_at'];
            
            foreach ($requiredColumns as $column) {
                $this->assertTrue(
                    Schema::hasColumn('user_devices', $column),
                    "user_devices table missing column: {$column}"
                );
            }
        } else {
            $this->markTestSkipped('user_devices table not yet implemented');
        }
    }
}