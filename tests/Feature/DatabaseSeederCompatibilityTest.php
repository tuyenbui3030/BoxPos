<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;

class DatabaseSeederCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all seeders can run without errors
     */
    public function test_database_seeder_runs_successfully(): void
    {
        // Run migrations first
        Artisan::call('migrate:fresh');
        
        // Run the main database seeder
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])
            ->assertExitCode(0);
        
        $this->assertTrue(true, 'Database seeder completed without errors');
    }

    /**
     * Test that required tables exist after seeding
     */
    public function test_required_tables_exist_after_seeding(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        $requiredTables = [
            'users',
            'stores', 
            'user_stores',
            'customers'
        ];
        
        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Required table '{$table}' does not exist after seeding"
            );
        }
    }

    /**
     * Test that seeded data maintains referential integrity
     */
    public function test_seeded_data_referential_integrity(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        // Test user_stores relationships
        $orphanedUserStores = \DB::table('user_stores')
            ->leftJoin('users', 'user_stores.user_id', '=', 'users.id')
            ->leftJoin('stores', 'user_stores.store_id', '=', 'stores.id')
            ->whereNull('users.id')
            ->orWhereNull('stores.id')
            ->count();
        
        $this->assertEquals(0, $orphanedUserStores, 'Found orphaned records in user_stores table');
        
        // Test customers store relationships
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'store_id')) {
            $orphanedCustomers = \DB::table('customers')
                ->leftJoin('stores', 'customers.store_id', '=', 'stores.id')
                ->whereNull('stores.id')
                ->whereNotNull('customers.store_id')
                ->count();
            
            $this->assertEquals(0, $orphanedCustomers, 'Found orphaned customers without valid store_id');
        }
    }

    /**
     * Test that multi-store data isolation is maintained
     */
    public function test_multi_store_data_isolation(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        // Verify multiple stores exist
        $storeCount = \DB::table('stores')->count();
        $this->assertGreaterThan(1, $storeCount, 'Multiple stores should exist for multi-store testing');
        
        // Check that business tables have store_id where expected
        $businessTables = ['customers'];
        
        foreach ($businessTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'store_id')) {
                $recordsWithoutStoreId = \DB::table($table)
                    ->whereNull('store_id')
                    ->count();
                
                $this->assertEquals(
                    0, 
                    $recordsWithoutStoreId, 
                    "Table '{$table}' has records without store_id"
                );
            }
        }
    }

    /**
     * Test that required enum values are valid
     */
    public function test_enum_values_are_valid(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        // Test store status values
        if (Schema::hasTable('stores') && Schema::hasColumn('stores', 'status')) {
            $invalidStatuses = \DB::table('stores')
                ->whereNotIn('status', ['active', 'inactive', 'suspended'])
                ->count();
            
            $this->assertEquals(0, $invalidStatuses, 'Found stores with invalid status values');
        }
        
        // Test user_stores role values
        if (Schema::hasTable('user_stores') && Schema::hasColumn('user_stores', 'role')) {
            $invalidRoles = \DB::table('user_stores')
                ->whereNotIn('role', ['admin', 'manager', 'staff', 'viewer'])
                ->count();
            
            $this->assertEquals(0, $invalidRoles, 'Found user_stores with invalid role values');
        }
        
        // Test customer type values
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'customer_type')) {
            $invalidTypes = \DB::table('customers')
                ->whereNotIn('customer_type', ['individual', 'company'])
                ->count();
            
            $this->assertEquals(0, $invalidTypes, 'Found customers with invalid customer_type values');
        }
    }

    /**
     * Test that unique constraints are respected
     */
    public function test_unique_constraints_are_respected(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        // Test user email uniqueness
        $duplicateEmails = \DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $this->assertEquals(0, $duplicateEmails, 'Found duplicate email addresses in users table');
        
        // Test store slug uniqueness
        $duplicateSlugs = \DB::table('stores')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        
        $this->assertEquals(0, $duplicateSlugs, 'Found duplicate slugs in stores table');
        
        // Test customer code uniqueness
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'customer_code')) {
            $duplicateCodes = \DB::table('customers')
                ->select('customer_code')
                ->whereNotNull('customer_code')
                ->groupBy('customer_code')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            $this->assertEquals(0, $duplicateCodes, 'Found duplicate customer codes');
        }
    }

    /**
     * Test that required fields are not null
     */
    public function test_required_fields_are_not_null(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        $requiredFields = [
            'users' => ['name', 'email'],
            'stores' => ['name', 'slug'],
            'customers' => ['customer_code', 'customer_name']
        ];
        
        foreach ($requiredFields as $table => $fields) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            foreach ($fields as $field) {
                if (!Schema::hasColumn($table, $field)) {
                    continue;
                }
                
                $nullCount = \DB::table($table)
                    ->whereNull($field)
                    ->orWhere($field, '')
                    ->count();
                
                $this->assertEquals(
                    0, 
                    $nullCount, 
                    "Found null/empty values in required field {$table}.{$field}"
                );
            }
        }
    }

    /**
     * Test that timestamps are properly set
     */
    public function test_timestamps_are_properly_set(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        $tablesWithTimestamps = ['users', 'stores', 'user_stores', 'customers'];
        
        foreach ($tablesWithTimestamps as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            if (Schema::hasColumn($table, 'created_at')) {
                $nullCreatedAt = \DB::table($table)
                    ->whereNull('created_at')
                    ->count();
                
                $this->assertEquals(
                    0, 
                    $nullCreatedAt, 
                    "Found records with null created_at in {$table}"
                );
            }
            
            if (Schema::hasColumn($table, 'updated_at')) {
                $nullUpdatedAt = \DB::table($table)
                    ->whereNull('updated_at')
                    ->count();
                
                $this->assertEquals(
                    0, 
                    $nullUpdatedAt, 
                    "Found records with null updated_at in {$table}"
                );
            }
        }
    }

    /**
     * Test that seeder can be run multiple times without errors
     */
    public function test_seeder_idempotency(): void
    {
        $this->artisan('migrate:fresh');
        
        // Run seeder first time
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])
            ->assertExitCode(0);
        
        $firstRunUserCount = \DB::table('users')->count();
        $firstRunStoreCount = \DB::table('stores')->count();
        
        // Run seeder second time
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class])
            ->assertExitCode(0);
        
        $secondRunUserCount = \DB::table('users')->count();
        $secondRunStoreCount = \DB::table('stores')->count();
        
        // Counts should be the same (seeder should handle duplicates)
        $this->assertEquals(
            $firstRunUserCount, 
            $secondRunUserCount, 
            'User count changed after running seeder twice'
        );
        
        $this->assertEquals(
            $firstRunStoreCount, 
            $secondRunStoreCount, 
            'Store count changed after running seeder twice'
        );
    }

    /**
     * Test that all expected demo data is created
     */
    public function test_demo_data_completeness(): void
    {
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
        
        // Verify demo stores exist
        $demoStores = \DB::table('stores')
            ->where('slug', 'like', 'demo%')
            ->count();
        
        $this->assertGreaterThan(0, $demoStores, 'Demo stores should be created');
        
        // Verify demo users exist
        $demoUsers = \DB::table('users')
            ->where('email', 'like', '%demo%')
            ->count();
        
        $this->assertGreaterThan(0, $demoUsers, 'Demo users should be created');
        
        // Verify user-store relationships exist
        $userStoreRelationships = \DB::table('user_stores')->count();
        $this->assertGreaterThan(0, $userStoreRelationships, 'User-store relationships should be created');
    }
}