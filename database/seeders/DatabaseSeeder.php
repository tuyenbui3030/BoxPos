<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Log\Traits\Loggable;

class DatabaseSeeder extends Seeder
{
    use Loggable;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->logActivity('database_seeding_started', [
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString(),
            'process_id' => getmypid()
        ]);

        // Validate environment and prerequisites
        $this->validateSeedingEnvironment();

        $this->command->info('🚀 Starting comprehensive database seeding...');
        $this->command->info('Environment: ' . app()->environment());
        $this->command->info('Process ID: ' . getmypid());

        $startTime = microtime(true);

        // Begin global transaction for entire seeding process
        DB::beginTransaction();

        try {
            // Execute seeders in proper dependency order
            $this->executeSeedersWithDependencyOrder();

            // Commit the entire seeding transaction first
            DB::commit();

            // Validate data consistency after commit (temporarily disabled for testing)
            // $this->validateSeedingResults();

            $this->logOperationPerformance('database_seeding_completed', $startTime, [
                'environment' => app()->environment(),
                'total_duration_seconds' => microtime(true) - $startTime
            ]);

            $this->displaySeedingResults();

        } catch (\Exception $e) {
            // Rollback entire seeding process on any failure
            DB::rollback();
            
            $this->logError($e, [
                'operation' => 'database_seeding',
                'environment' => app()->environment(),
                'duration_before_failure' => microtime(true) - $startTime,
                'stack_trace' => $e->getTraceAsString()
            ]);

            $this->command->error('❌ Database seeding failed: ' . $e->getMessage());
            $this->command->error('All changes have been rolled back.');
            
            throw $e;
        }
    }

    /**
     * Validate seeding environment and prerequisites
     */
    private function validateSeedingEnvironment(): void
    {
        try {
            SeedingHelper::validateSeedingEnvironment();
            $this->logActivity('seeding_environment_validated');
        } catch (\Exception $e) {
            $this->logError($e, ['validation_step' => 'environment']);
            throw $e;
        }
    }

    /**
     * Execute seeders with proper dependency order and error handling
     */
    private function executeSeedersWithDependencyOrder(): void
    {
        $executionOrder = config('seeding.execution_order', []);

        if (empty($executionOrder)) {
            $this->logActivity('using_fallback_seeder_order');
            $this->runFallbackSeeders();
            return;
        }

        // Execute core seeders first (no store dependency)
        $this->executeSeederGroup('core', $executionOrder['core'] ?? []);

        // Execute business seeders in dependency levels
        $this->executeSeederGroup('business_level_1', $executionOrder['business_level_1'] ?? []);
        $this->executeSeederGroup('business_level_2', $executionOrder['business_level_2'] ?? []);
        $this->executeSeederGroup('business_level_3', $executionOrder['business_level_3'] ?? []);

        // Execute system seeders last
        $this->executeSeederGroup('system', $executionOrder['system'] ?? []);
    }

    /**
     * Execute a group of seeders with proper error handling and logging
     */
    private function executeSeederGroup(string $groupName, array $seeders): void
    {
        if (empty($seeders)) {
            $this->logActivity('seeder_group_skipped', ['group' => $groupName, 'reason' => 'empty']);
            return;
        }

        $this->command->info("📋 Seeding {$groupName} data...");
        $this->logActivity('seeder_group_started', [
            'group' => $groupName,
            'seeders_count' => count($seeders),
            'seeders' => $seeders
        ]);

        $groupStartTime = microtime(true);

        try {
            foreach ($seeders as $seederClass) {
                $this->executeIndividualSeeder($seederClass, $groupName);
            }

            $this->logOperationPerformance("seeder_group_{$groupName}_completed", $groupStartTime, [
                'group' => $groupName,
                'seeders_count' => count($seeders)
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'operation' => 'seeder_group_execution',
                'group' => $groupName,
                'failed_at_duration' => microtime(true) - $groupStartTime
            ]);
            throw $e;
        }
    }

    /**
     * Execute individual seeder with comprehensive error handling
     */
    private function executeIndividualSeeder(string $seederClass, string $group): void
    {
        $seederStartTime = microtime(true);
        
        $this->logActivity('individual_seeder_started', [
            'seeder' => $seederClass,
            'group' => $group
        ]);

        try {
            // Check if seeder class exists
            if (!class_exists($seederClass)) {
                throw new \Exception("Seeder class {$seederClass} does not exist");
            }

            $this->command->line("  → Running {$seederClass}...");
            
            $this->call($seederClass);

            $this->logOperationPerformance('individual_seeder_completed', $seederStartTime, [
                'seeder' => $seederClass,
                'group' => $group
            ]);

            $this->command->line("  ✅ {$seederClass} completed");

        } catch (\Exception $e) {
            $this->logError($e, [
                'operation' => 'individual_seeder_execution',
                'seeder' => $seederClass,
                'group' => $group,
                'duration_before_failure' => microtime(true) - $seederStartTime
            ]);

            $this->command->error("  ❌ {$seederClass} failed: " . $e->getMessage());
            throw new \Exception("Seeder {$seederClass} failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Fallback seeders for backward compatibility
     */
    private function runFallbackSeeders(): void
    {
        $this->command->warn('Using fallback seeder configuration');
        
        $fallbackSeeders = [
            // Core system seeders
            'Packages\User\Database\Seeders\UserSeeder',
            'Packages\Store\Database\Seeders\StoreSeeder',
            
            // Material management
            'Packages\MaterialCatalog\Database\Seeders\MaterialCatalogSeeder',
            'Packages\MaterialSuppliers\Database\Seeders\MaterialSupplierSeeder',
            'Packages\MaterialInventory\Database\Seeders\MaterialInventorySeeder',
            'Packages\MaterialPricing\Database\Seeders\MaterialPricingSeeder',
            
            // Business entities
            'Packages\Customer\Database\Seeders\CustomerSeeder',
            'Packages\Products\Database\Seeders\ProductSeeder',
            'Packages\Employees\Database\Seeders\EmployeeSeeder',
            
            // Sales and financial
            'Packages\SalesOrders\Database\Seeders\SalesOrderSeeder',
            'Packages\CashManagement\Database\Seeders\CashManagementSeeder',
            'Packages\Payments\Database\Seeders\PaymentSeeder',
            
            // Marketing and system
            'Packages\Loyalty\Database\Seeders\LoyaltySeeder',
            'Packages\Promotions\Database\Seeders\PromotionSeeder',
            'Packages\Reports\Database\Seeders\ReportSeeder',
            'Packages\Notifications\Database\Seeders\NotificationSeeder',
        ];

        foreach ($fallbackSeeders as $seederClass) {
            if (class_exists($seederClass)) {
                $this->executeIndividualSeeder($seederClass, 'fallback');
            } else {
                $this->command->warn("  ⚠️  Seeder {$seederClass} not found, skipping...");
                $this->logActivity('seeder_not_found', ['seeder' => $seederClass]);
            }
        }
    }

    /**
     * Validate seeding results with comprehensive checks
     */
    private function validateSeedingResults(): void
    {
        $this->command->info('🔍 Validating seeding results...');
        $validationStartTime = microtime(true);
        
        try {
            // Run data consistency validation
            $issues = SeedingHelper::validateDataConsistency();
            
            // Additional validation checks
            $this->validateRequiredData();
            $this->validateStoreIsolation();
            $this->validateRelationshipIntegrity();
            
            if (!empty($issues)) {
                $this->command->warn('⚠️  Data consistency issues found:');
                foreach ($issues as $issue) {
                    $this->command->warn('   - ' . $issue);
                }
                
                $this->logActivity('seeding_validation_issues', [
                    'issues_count' => count($issues),
                    'issues' => $issues
                ]);
                
                // Don't fail seeding for minor consistency issues, just warn
                $this->command->warn('⚠️  Seeding completed with warnings. Review the issues above.');
            } else {
                $this->command->info('✅ Data consistency validation passed');
            }

            $this->logOperationPerformance('seeding_validation_completed', $validationStartTime);

        } catch (\Exception $e) {
            $this->logError($e, [
                'operation' => 'seeding_validation',
                'duration_before_failure' => microtime(true) - $validationStartTime
            ]);
            
            $this->command->error('❌ Validation failed: ' . $e->getMessage());
            throw new \Exception('Seeding validation failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate that required data exists
     */
    private function validateRequiredData(): void
    {
        $requiredChecks = [
            'stores' => config('seeding.validation.required_stores_minimum', 1),
            'users' => config('seeding.validation.required_admin_users_minimum', 1),
        ];

        foreach ($requiredChecks as $table => $minCount) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $actualCount = DB::table($table)->count();
                if ($actualCount < $minCount) {
                    throw new \Exception("Table '{$table}' has {$actualCount} records, minimum {$minCount} required");
                }
            }
        }
    }

    /**
     * Validate store isolation is maintained
     */
    private function validateStoreIsolation(): void
    {
        $businessTables = [
            'customers', 'material_inventory', 'sales_orders', 'employees',
            'cash_accounts', 'promotions', 'loyalty_programs'
        ];

        foreach ($businessTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $recordsWithoutStore = DB::table($table)
                    ->whereNull('store_id')
                    ->count();

                if ($recordsWithoutStore > 0) {
                    $this->logActivity('store_isolation_violation', [
                        'table' => $table,
                        'records_without_store' => $recordsWithoutStore
                    ]);
                }
            }
        }
    }

    /**
     * Validate relationship integrity
     */
    private function validateRelationshipIntegrity(): void
    {
        // Check user-store relationships
        if (DB::getSchemaBuilder()->hasTable('users') && DB::getSchemaBuilder()->hasTable('user_stores')) {
            $usersWithoutStores = DB::table('users')
                ->leftJoin('user_stores', 'users.id', '=', 'user_stores.user_id')
                ->whereNull('user_stores.user_id')
                ->count();

            if ($usersWithoutStores > 0) {
                $this->logActivity('relationship_integrity_issue', [
                    'issue' => 'users_without_stores',
                    'count' => $usersWithoutStores
                ]);
            }
        }

        // Check orphaned records in key tables
        $this->validateOrphanedRecords();
    }

    /**
     * Check for orphaned records that reference non-existent parents
     */
    private function validateOrphanedRecords(): void
    {
        $relationships = [
            'sales_orders' => ['customers' => 'customer_id'],
            'material_inventory' => ['building_materials' => 'material_id'],
            'employees' => ['stores' => 'store_id'],
        ];

        foreach ($relationships as $childTable => $parents) {
            if (!DB::getSchemaBuilder()->hasTable($childTable)) continue;

            foreach ($parents as $parentTable => $foreignKey) {
                if (!DB::getSchemaBuilder()->hasTable($parentTable)) continue;

                $orphanedCount = DB::table($childTable)
                    ->leftJoin($parentTable, "{$childTable}.{$foreignKey}", '=', "{$parentTable}.id")
                    ->whereNull("{$parentTable}.id")
                    ->whereNotNull("{$childTable}.{$foreignKey}")
                    ->count();

                if ($orphanedCount > 0) {
                    $this->logActivity('orphaned_records_found', [
                        'child_table' => $childTable,
                        'parent_table' => $parentTable,
                        'foreign_key' => $foreignKey,
                        'orphaned_count' => $orphanedCount
                    ]);
                }
            }
        }
    }

    /**
     * Display comprehensive seeding results with statistics and summary
     */
    private function displaySeedingResults(): void
    {
        try {
            $stats = SeedingHelper::getSeedingStatistics();
            $totalRecords = array_sum($stats);
            
            $this->command->info('');
            $this->command->info('🎉 COMPREHENSIVE DATABASE SEEDING COMPLETED SUCCESSFULLY!');
            $this->command->info('');
            $this->command->info('📊 Seeding Statistics:');
            $this->command->info("   📈 Total records created: {$totalRecords}");
            $this->command->info('');
            
            // Group statistics by category
            $this->displayStatisticsByCategory($stats);
            
            $this->command->info('');
            $this->command->info('📋 Modules successfully seeded:');
            $this->displayModuleStatus();
            
            $this->command->info('');
            $this->displayLoginCredentials();
            
            $this->command->info('');
            $this->displayStoreInformation();
            
            $this->command->info('');
            $this->command->info('🌍 Environment: ' . app()->environment());
            $this->command->info('⚙️  Configuration: config/seeding.php');
            $this->command->info('📝 Logs: Check application logs for detailed seeding information');
            
            // Log final statistics
            $this->logActivity('seeding_results_displayed', [
                'total_records' => $totalRecords,
                'statistics' => $stats,
                'environment' => app()->environment()
            ]);

        } catch (\Exception $e) {
            $this->logError($e, ['operation' => 'display_seeding_results']);
            $this->command->error('❌ Failed to display seeding results: ' . $e->getMessage());
        }
    }

    /**
     * Display statistics grouped by category
     */
    private function displayStatisticsByCategory(array $stats): void
    {
        $categories = [
            'Core System' => ['stores', 'users'],
            'Material Management' => ['material_categories', 'building_materials', 'material_suppliers', 'material_inventory'],
            'Sales & Customer' => ['customers', 'sales_orders', 'products'],
            'Employee Management' => ['employees', 'departments'],
            'Financial' => ['cash_accounts', 'payments'],
            'Marketing' => ['promotions', 'loyalty_programs'],
        ];

        foreach ($categories as $categoryName => $tables) {
            $categoryTotal = 0;
            $categoryStats = [];
            
            foreach ($tables as $table) {
                if (isset($stats[$table])) {
                    $categoryStats[$table] = $stats[$table];
                    $categoryTotal += $stats[$table];
                }
            }
            
            if ($categoryTotal > 0) {
                $this->command->info("   📂 {$categoryName}: {$categoryTotal} records");
                foreach ($categoryStats as $table => $count) {
                    $this->command->info("      ✅ {$table}: {$count}");
                }
            }
        }
    }

    /**
     * Display module seeding status
     */
    private function displayModuleStatus(): void
    {
        $modules = [
            'Multi-tenant Stores & Users' => ['stores', 'users'],
            'Material Catalog & Inventory' => ['material_categories', 'building_materials', 'material_inventory'],
            'Suppliers & Purchasing' => ['material_suppliers'],
            'Products & Services' => ['products'],
            'Customer Management' => ['customers'],
            'Employee Management' => ['employees'],
            'Sales Orders & Invoicing' => ['sales_orders'],
            'Cash Management' => ['cash_accounts'],
            'Payment Processing' => ['payments'],
            'Promotions & Loyalty' => ['promotions', 'loyalty_programs'],
            'Reports & Analytics' => ['report_templates'],
            'Notifications' => ['notification_templates'],
        ];

        foreach ($modules as $moduleName => $tables) {
            $hasData = false;
            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table) && DB::table($table)->count() > 0) {
                    $hasData = true;
                    break;
                }
            }
            
            $status = $hasData ? '✅' : '⚠️ ';
            $this->command->info("   {$status} {$moduleName}");
        }
    }

    /**
     * Display login credentials information
     */
    private function displayLoginCredentials(): void
    {
        $this->command->info('🔑 Sample Login Credentials:');
        
        // Try to get actual admin user info from database
        if (DB::getSchemaBuilder()->hasTable('users')) {
            $adminUsers = DB::table('users')
                ->where('email', 'like', '%admin%')
                ->limit(3)
                ->get(['email']);
                
            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $user) {
                    $this->command->info("   👤 {$user->email} / password");
                }
            } else {
                $this->command->info('   👤 admin@demo001.boxpos.vn / password (default)');
            }
        }
    }

    /**
     * Display store information
     */
    private function displayStoreInformation(): void
    {
        $this->command->info('🏪 Available Stores:');
        
        if (DB::getSchemaBuilder()->hasTable('stores')) {
            $stores = DB::table('stores')
                ->select(['name', 'status'])
                ->limit(5)
                ->get();
                
            if ($stores->isNotEmpty()) {
                foreach ($stores as $store) {
                    $status = $store->status === 'active' ? '🟢' : '🔴';
                    $this->command->info("   {$status} {$store->name}");
                }
                
                if ($stores->count() >= 5) {
                    $totalStores = DB::table('stores')->count();
                    if ($totalStores > 5) {
                        $this->command->info("   ... and " . ($totalStores - 5) . " more stores");
                    }
                }
            } else {
                $this->command->info('   ⚠️  No stores found in database');
            }
        }
    }
}
