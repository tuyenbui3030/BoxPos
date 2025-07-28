<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ValidateDatabaseSchema extends Command
{
    protected $signature = 'db:validate-schema {--fix : Attempt to fix validation issues}';
    protected $description = 'Validate database schema integrity and constraints';

    private array $validationErrors = [];
    private array $validationWarnings = [];

    public function handle()
    {
        $this->info('🔍 Starting database schema validation...');
        
        $this->validateMultiStoreArchitecture();
        $this->validateForeignKeyConstraints();
        $this->validateRequiredIndexes();
        $this->validateDataIntegrity();
        $this->validateBusinessRules();
        
        $this->displayResults();
        
        if ($this->option('fix') && !empty($this->validationErrors)) {
            $this->attemptFixes();
        }
        
        return empty($this->validationErrors) ? 0 : 1;
    }

    private function validateMultiStoreArchitecture(): void
    {
        $this->info('📋 Validating multi-store architecture...');
        
        // Check if stores table exists
        if (!Schema::hasTable('stores')) {
            $this->addError('stores table is missing - required for multi-store architecture');
            return;
        }
        
        // Check if user_stores table exists
        if (!Schema::hasTable('user_stores')) {
            $this->addError('user_stores table is missing - required for user-store relationships');
        }
        
        // Validate store_id columns on business tables
        $businessTables = $this->getBusinessTables();
        foreach ($businessTables as $table) {
            if (!Schema::hasColumn($table, 'store_id')) {
                $this->addWarning("Table '{$table}' missing store_id column for multi-store isolation");
            } else {
                // Check if store_id has proper foreign key constraint
                if (!$this->hasForeignKeyConstraint($table, 'store_id', 'stores', 'id')) {
                    $this->addWarning("Table '{$table}' store_id column lacks foreign key constraint to stores table");
                }
            }
        }
        
        // Check if users table has current_store_id
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'current_store_id')) {
            $this->addWarning('users table missing current_store_id column for store context');
        }
    }

    private function validateForeignKeyConstraints(): void
    {
        $this->info('🔗 Validating foreign key constraints...');
        
        $expectedConstraints = [
            'user_stores' => [
                ['user_id', 'users', 'id'],
                ['store_id', 'stores', 'id']
            ],
            'customers' => [
                ['created_by', 'users', 'id'],
                ['store_id', 'stores', 'id']
            ],
            'user_devices' => [
                ['user_id', 'users', 'id']
            ]
        ];
        
        foreach ($expectedConstraints as $table => $constraints) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            foreach ($constraints as [$column, $referencedTable, $referencedColumn]) {
                if (Schema::hasColumn($table, $column)) {
                    if (!$this->hasForeignKeyConstraint($table, $column, $referencedTable, $referencedColumn)) {
                        $this->addError("Missing foreign key constraint: {$table}.{$column} → {$referencedTable}.{$referencedColumn}");
                    }
                }
            }
        }
    }

    private function validateRequiredIndexes(): void
    {
        $this->info('📊 Validating required indexes...');
        
        $requiredIndexes = [
            'users' => ['email'],
            'stores' => ['slug', 'domain', 'status'],
            'user_stores' => ['user_id', 'store_id', 'role'],
            'customers' => ['customer_code', 'store_id', 'customer_name']
        ];
        
        foreach ($requiredIndexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    if (!$this->hasIndex($table, $column)) {
                        $this->addWarning("Missing recommended index on {$table}.{$column}");
                    }
                }
            }
        }
    }

    private function validateDataIntegrity(): void
    {
        $this->info('🛡️ Validating data integrity...');
        
        // Check for orphaned records
        $this->validateOrphanedRecords();
        
        // Check for duplicate unique constraints
        $this->validateUniqueConstraints();
        
        // Check for null values in required fields
        $this->validateRequiredFields();
    }

    private function validateOrphanedRecords(): void
    {
        $relationships = [
            'user_stores' => [
                ['user_id', 'users', 'id'],
                ['store_id', 'stores', 'id']
            ],
            'customers' => [
                ['store_id', 'stores', 'id']
            ]
        ];
        
        foreach ($relationships as $table => $constraints) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            foreach ($constraints as [$column, $referencedTable, $referencedColumn]) {
                if (!Schema::hasTable($referencedTable) || !Schema::hasColumn($table, $column)) {
                    continue;
                }
                
                $orphanedCount = DB::table($table)
                    ->leftJoin($referencedTable, "{$table}.{$column}", '=', "{$referencedTable}.{$referencedColumn}")
                    ->whereNull("{$referencedTable}.{$referencedColumn}")
                    ->whereNotNull("{$table}.{$column}")
                    ->count();
                
                if ($orphanedCount > 0) {
                    $this->addError("Found {$orphanedCount} orphaned records in {$table}.{$column}");
                }
            }
        }
    }

    private function validateUniqueConstraints(): void
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
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                
                $duplicates = DB::table($table)
                    ->select($column)
                    ->whereNotNull($column)
                    ->groupBy($column)
                    ->havingRaw('COUNT(*) > 1')
                    ->count();
                
                if ($duplicates > 0) {
                    $this->addError("Found duplicate values in unique column {$table}.{$column}");
                }
            }
        }
    }

    private function validateRequiredFields(): void
    {
        $requiredFields = [
            'users' => ['name', 'email'],
            'stores' => ['name', 'slug'],
            'customers' => ['customer_code', 'customer_name']
        ];
        
        foreach ($requiredFields as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                
                $nullCount = DB::table($table)
                    ->whereNull($column)
                    ->orWhere($column, '')
                    ->count();
                
                if ($nullCount > 0) {
                    $this->addError("Found {$nullCount} null/empty values in required field {$table}.{$column}");
                }
            }
        }
    }

    private function validateBusinessRules(): void
    {
        $this->info('💼 Validating business rules...');
        
        // Validate store status values
        if (Schema::hasTable('stores') && Schema::hasColumn('stores', 'status')) {
            $invalidStatuses = DB::table('stores')
                ->whereNotIn('status', ['active', 'inactive', 'suspended'])
                ->count();
            
            if ($invalidStatuses > 0) {
                $this->addError("Found {$invalidStatuses} stores with invalid status values");
            }
        }
        
        // Validate user_stores roles
        if (Schema::hasTable('user_stores') && Schema::hasColumn('user_stores', 'role')) {
            $invalidRoles = DB::table('user_stores')
                ->whereNotIn('role', ['admin', 'manager', 'staff', 'viewer'])
                ->count();
            
            if ($invalidRoles > 0) {
                $this->addError("Found {$invalidRoles} user_stores with invalid role values");
            }
        }
        
        // Validate customer types
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'customer_type')) {
            $invalidTypes = DB::table('customers')
                ->whereNotIn('customer_type', ['individual', 'company'])
                ->count();
            
            if ($invalidTypes > 0) {
                $this->addError("Found {$invalidTypes} customers with invalid customer_type values");
            }
        }
    }

    private function getBusinessTables(): array
    {
        $allTables = Schema::getTableListing();
        $systemTables = ['cache', 'jobs', 'sessions', 'password_reset_tokens', 'migrations'];
        
        return array_filter($allTables, function ($table) use ($systemTables) {
            return !in_array($table, $systemTables);
        });
    }

    private function hasForeignKeyConstraint(string $table, string $column, string $referencedTable, string $referencedColumn): bool
    {
        try {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $tableDetails = $schemaManager->listTableDetails($table);
            
            foreach ($tableDetails->getForeignKeys() as $foreignKey) {
                if (in_array($column, $foreignKey->getLocalColumns()) &&
                    $foreignKey->getForeignTableName() === $referencedTable &&
                    in_array($referencedColumn, $foreignKey->getForeignColumns())) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function hasIndex(string $table, string $column): bool
    {
        try {
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $schemaManager->listTableIndexes($table);
            
            foreach ($indexes as $index) {
                if (in_array($column, $index->getColumns())) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function addError(string $message): void
    {
        $this->validationErrors[] = $message;
    }

    private function addWarning(string $message): void
    {
        $this->validationWarnings[] = $message;
    }

    private function displayResults(): void
    {
        $this->info('');
        $this->info('📋 Validation Results:');
        $this->info('');
        
        if (empty($this->validationErrors) && empty($this->validationWarnings)) {
            $this->info('✅ All validations passed! Database schema is healthy.');
            return;
        }
        
        if (!empty($this->validationErrors)) {
            $this->error('❌ Validation Errors:');
            foreach ($this->validationErrors as $error) {
                $this->error("  • {$error}");
            }
            $this->info('');
        }
        
        if (!empty($this->validationWarnings)) {
            $this->warn('⚠️  Validation Warnings:');
            foreach ($this->validationWarnings as $warning) {
                $this->warn("  • {$warning}");
            }
            $this->info('');
        }
        
        $this->info("Total Errors: " . count($this->validationErrors));
        $this->info("Total Warnings: " . count($this->validationWarnings));
    }

    private function attemptFixes(): void
    {
        $this->info('🔧 Attempting to fix validation issues...');
        
        // This is a placeholder for fix implementations
        // In a real scenario, you would implement specific fixes for each type of error
        
        $this->warn('Automatic fixes not implemented yet. Please review errors manually.');
    }
}