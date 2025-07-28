<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GenerateDatabaseDocumentation extends Command
{
    protected $signature = 'db:generate-docs {--output=database-documentation.md}';
    protected $description = 'Generate comprehensive database documentation from existing migrations';

    public function handle()
    {
        $this->info('🚀 Generating comprehensive database documentation...');
        
        $documentation = $this->generateDocumentation();
        
        $outputPath = $this->option('output');
        File::put($outputPath, $documentation);
        
        $this->info("✅ Database documentation generated successfully at: {$outputPath}");
        
        return 0;
    }

    private function generateDocumentation(): string
    {
        $doc = "# BoxPos Database Schema Documentation\n\n";
        $doc .= "Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        $doc .= "## Overview\n\n";
        $doc .= "This document provides comprehensive documentation for the BoxPos database schema. ";
        $doc .= "BoxPos is a multi-store point-of-sale system for building material stores with support for ";
        $doc .= "inventory management, sales processing, employee management, and customer loyalty programs.\n\n";
        
        $doc .= "## Architecture\n\n";
        $doc .= "### Multi-Store Architecture\n";
        $doc .= "- **Store Isolation**: Most business tables include `store_id` for data separation\n";
        $doc .= "- **User-Store Relationships**: Users can belong to multiple stores via `user_stores` table\n";
        $doc .= "- **Current Store Context**: Users have `current_store_id` for active store selection\n\n";
        
        $doc .= $this->generateTablesDocumentation();
        $doc .= $this->generateRelationshipsDocumentation();
        $doc .= $this->generateIndexesDocumentation();
        
        return $doc;
    }

    private function generateTablesDocumentation(): string
    {
        $doc = "## Database Tables\n\n";
        
        $tables = $this->getAllTables();
        $categorizedTables = $this->categorizeTables($tables);
        
        foreach ($categorizedTables as $category => $tableList) {
            $doc .= "### {$category}\n\n";
            
            foreach ($tableList as $table) {
                $doc .= $this->generateTableDocumentation($table);
            }
        }
        
        return $doc;
    }

    private function getAllTables(): array
    {
        return Schema::getTableListing();
    }

    private function categorizeTables(array $tables): array
    {
        $categories = [
            'Core System Tables' => [],
            'User & Authentication' => [],
            'Store Management' => [],
            'Material Management' => [],
            'Inventory & Warehouse' => [],
            'Sales & Orders' => [],
            'Customer Management' => [],
            'Employee Management' => [],
            'Financial Management' => [],
            'Marketing & Loyalty' => [],
            'Reporting & Analytics' => [],
            'Notifications' => [],
            'System Tables' => []
        ];

        foreach ($tables as $table) {
            $category = $this->determineTableCategory($table);
            $categories[$category][] = $table;
        }

        return array_filter($categories);
    }

    private function determineTableCategory(string $table): string
    {
        $patterns = [
            'Core System Tables' => ['cache', 'jobs', 'sessions', 'password_reset_tokens'],
            'User & Authentication' => ['users', 'user_stores', 'user_devices'],
            'Store Management' => ['stores'],
            'Material Management' => ['material_', 'building_materials'],
            'Inventory & Warehouse' => ['inventory_', 'stock_', 'warehouse'],
            'Sales & Orders' => ['sales_', 'orders', 'invoices'],
            'Customer Management' => ['customers', 'customer_'],
            'Employee Management' => ['employees', 'employee_', 'departments', 'positions'],
            'Financial Management' => ['cash_', 'payments', 'payment_'],
            'Marketing & Loyalty' => ['promotions', 'loyalty_'],
            'Reporting & Analytics' => ['report_', 'dashboard'],
            'Notifications' => ['notification']
        ];

        foreach ($patterns as $category => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (Str::startsWith($table, $prefix) || $table === $prefix) {
                    return $category;
                }
            }
        }

        return 'System Tables';
    }

    private function generateTableDocumentation(string $table): string
    {
        $doc = "#### `{$table}`\n\n";
        
        try {
            $columns = Schema::getColumnListing($table);
            $columnDetails = $this->getColumnDetails($table);
            
            $doc .= "**Purpose**: " . $this->getTablePurpose($table) . "\n\n";
            
            $doc .= "| Column | Type | Nullable | Default | Description |\n";
            $doc .= "|--------|------|----------|---------|-------------|\n";
            
            foreach ($columns as $column) {
                $details = $columnDetails[$column] ?? [];
                $type = $details['type'] ?? 'unknown';
                $nullable = ($details['nullable'] ?? false) ? 'Yes' : 'No';
                $default = $details['default'] ?? 'NULL';
                $description = $this->getColumnDescription($table, $column);
                
                $doc .= "| `{$column}` | {$type} | {$nullable} | {$default} | {$description} |\n";
            }
            
            $doc .= "\n";
            
            // Add foreign keys if any
            $foreignKeys = $this->getForeignKeys($table);
            if (!empty($foreignKeys)) {
                $doc .= "**Foreign Keys**:\n";
                foreach ($foreignKeys as $fk) {
                    $doc .= "- `{$fk['column']}` → `{$fk['referenced_table']}.{$fk['referenced_column']}`\n";
                }
                $doc .= "\n";
            }
            
            // Add indexes
            $indexes = $this->getTableIndexes($table);
            if (!empty($indexes)) {
                $doc .= "**Indexes**:\n";
                foreach ($indexes as $index) {
                    $doc .= "- `{$index['name']}` on `{$index['columns']}`" . 
                           ($index['unique'] ? ' (UNIQUE)' : '') . "\n";
                }
                $doc .= "\n";
            }
            
        } catch (\Exception $e) {
            $doc .= "*Error generating documentation for this table: {$e->getMessage()}*\n\n";
        }
        
        return $doc;
    }

    private function getColumnDetails(string $table): array
    {
        try {
            $columns = [];
            $tableColumns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table);
            
            foreach ($tableColumns as $column) {
                $columns[$column->getName()] = [
                    'type' => $column->getType()->getName(),
                    'nullable' => !$column->getNotnull(),
                    'default' => $column->getDefault()
                ];
            }
            
            return $columns;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTablePurpose(string $table): string
    {
        $purposes = [
            'users' => 'Stores user authentication and basic profile information',
            'stores' => 'Contains store/tenant information for multi-store architecture',
            'user_stores' => 'Many-to-many relationship between users and stores with role assignments',
            'customers' => 'Customer information and transaction history',
            'sessions' => 'Laravel session storage',
            'cache' => 'Laravel cache storage',
            'jobs' => 'Laravel queue jobs',
            'password_reset_tokens' => 'Password reset token storage',
            'user_devices' => 'Tracks user login devices for security'
        ];

        return $purposes[$table] ?? 'Business data table for ' . Str::title(str_replace('_', ' ', $table));
    }

    private function getColumnDescription(string $table, string $column): string
    {
        $descriptions = [
            'id' => 'Primary key',
            'store_id' => 'Foreign key to stores table for multi-tenant isolation',
            'user_id' => 'Foreign key to users table',
            'created_at' => 'Record creation timestamp',
            'updated_at' => 'Record last update timestamp',
            'deleted_at' => 'Soft delete timestamp',
            'created_by' => 'User who created this record',
            'updated_by' => 'User who last updated this record',
            'name' => 'Display name',
            'email' => 'Email address',
            'phone' => 'Phone number',
            'address' => 'Physical address',
            'status' => 'Record status',
            'is_active' => 'Active/inactive flag'
        ];

        return $descriptions[$column] ?? 'Data field for ' . Str::title(str_replace('_', ' ', $column));
    }

    private function getForeignKeys(string $table): array
    {
        try {
            $foreignKeys = [];
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $tableDetails = $schemaManager->listTableDetails($table);
            
            foreach ($tableDetails->getForeignKeys() as $foreignKey) {
                $foreignKeys[] = [
                    'column' => implode(', ', $foreignKey->getLocalColumns()),
                    'referenced_table' => $foreignKey->getForeignTableName(),
                    'referenced_column' => implode(', ', $foreignKey->getForeignColumns())
                ];
            }
            
            return $foreignKeys;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTableIndexes(string $table): array
    {
        try {
            $indexes = [];
            $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
            $tableIndexes = $schemaManager->listTableIndexes($table);
            
            foreach ($tableIndexes as $index) {
                if (!$index->isPrimary()) {
                    $indexes[] = [
                        'name' => $index->getName(),
                        'columns' => implode(', ', $index->getColumns()),
                        'unique' => $index->isUnique()
                    ];
                }
            }
            
            return $indexes;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function generateRelationshipsDocumentation(): string
    {
        $doc = "## Entity Relationships\n\n";
        
        $doc .= "### Core Relationships\n\n";
        $doc .= "```mermaid\n";
        $doc .= "erDiagram\n";
        $doc .= "    STORES ||--o{ USER_STORES : has\n";
        $doc .= "    USERS ||--o{ USER_STORES : belongs_to\n";
        $doc .= "    USERS ||--o{ USER_DEVICES : owns\n";
        $doc .= "    STORES ||--o{ CUSTOMERS : has\n";
        $doc .= "    USERS ||--o{ CUSTOMERS : created_by\n";
        $doc .= "```\n\n";
        
        $doc .= "### Multi-Store Data Isolation\n\n";
        $doc .= "Most business tables include a `store_id` foreign key that references the `stores.id` field. ";
        $doc .= "This ensures data isolation between different store tenants:\n\n";
        
        $tablesWithStoreId = $this->getTablesWithStoreId();
        foreach ($tablesWithStoreId as $table) {
            $doc .= "- `{$table}` → `stores.id`\n";
        }
        
        $doc .= "\n";
        
        return $doc;
    }

    private function getTablesWithStoreId(): array
    {
        $tables = [];
        foreach ($this->getAllTables() as $table) {
            try {
                if (Schema::hasColumn($table, 'store_id')) {
                    $tables[] = $table;
                }
            } catch (\Exception $e) {
                // Skip tables that can't be checked
            }
        }
        return $tables;
    }

    private function generateIndexesDocumentation(): string
    {
        $doc = "## Performance Indexes\n\n";
        $doc .= "The following indexes are created to optimize query performance:\n\n";
        
        foreach ($this->getAllTables() as $table) {
            $indexes = $this->getTableIndexes($table);
            if (!empty($indexes)) {
                $doc .= "### `{$table}`\n";
                foreach ($indexes as $index) {
                    $doc .= "- `{$index['name']}` on `{$index['columns']}`" . 
                           ($index['unique'] ? ' (UNIQUE)' : '') . "\n";
                }
                $doc .= "\n";
            }
        }
        
        return $doc;
    }
}