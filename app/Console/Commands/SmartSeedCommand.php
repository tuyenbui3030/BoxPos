<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Exception;

class SmartSeedCommand extends Command
{
    protected $signature = 'db:smart-seed {--fresh : Drop all tables and recreate}';
    protected $description = 'Intelligently seed the database with error handling and auto-fixing';

    private array $seeders = [
        'StoreSeeder',
        'UserSeeder', 
        'UserStoreSeeder',
        'MaterialSeeder',
        'MaterialInventorySeeder',
        'MaterialPricingSeeder',
        'MaterialPurchasingSeeder',
        'ProductSeeder',
        'CustomerSeeder',
        'EmployeeSeeder',
        'EmployeeTimesheetSeeder',
        'CashManagementSeeder',
        'SalesSeeder',
        'ReportSeeder',
        'PromotionSeeder',
        'LoyaltySeeder',
        'PaymentSeeder',
        'NotificationSeeder',
    ];

    private array $completedSeeders = [];
    private array $failedSeeders = [];

    public function handle()
    {
        $this->info('🚀 Starting Smart Database Seeding...');

        if ($this->option('fresh')) {
            $this->info('🔄 Running fresh migration...');
            Artisan::call('migrate:fresh');
            $this->info('✅ Fresh migration completed');
        }

        foreach ($this->seeders as $seeder) {
            $this->runSeeder($seeder);
        }

        $this->displaySummary();
        
        return $this->failedSeeders ? 1 : 0;
    }

    private function runSeeder(string $seeder): void
    {
        $this->info("🌱 Running {$seeder}...");
        
        try {
            Artisan::call('db:seed', ['--class' => $seeder]);
            $this->completedSeeders[] = $seeder;
            $this->info("✅ {$seeder} completed successfully");
            
        } catch (Exception $e) {
            $this->error("❌ {$seeder} failed: " . $e->getMessage());
            
            // Try to auto-fix common issues
            if ($this->attemptAutoFix($seeder, $e)) {
                $this->info("🔧 Auto-fix applied, retrying {$seeder}...");
                try {
                    Artisan::call('db:seed', ['--class' => $seeder]);
                    $this->completedSeeders[] = $seeder;
                    $this->info("✅ {$seeder} completed after auto-fix");
                } catch (Exception $retryException) {
                    $this->failedSeeders[] = [
                        'seeder' => $seeder,
                        'error' => $retryException->getMessage()
                    ];
                    $this->error("❌ {$seeder} still failed after auto-fix");
                }
            } else {
                $this->failedSeeders[] = [
                    'seeder' => $seeder,
                    'error' => $e->getMessage()
                ];
            }
        }
    }

    private function attemptAutoFix(string $seeder, Exception $e): bool
    {
        $errorMessage = $e->getMessage();
        
        // Handle duplicate entry errors
        if (str_contains($errorMessage, 'Duplicate entry')) {
            return $this->fixDuplicateEntries($seeder, $errorMessage);
        }
        
        // Handle column not found errors
        if (str_contains($errorMessage, 'Column not found')) {
            return $this->fixColumnNotFound($seeder, $errorMessage);
        }
        
        // Handle foreign key constraint errors
        if (str_contains($errorMessage, 'foreign key constraint')) {
            return $this->fixForeignKeyConstraint($seeder, $errorMessage);
        }
        
        return false;
    }

    private function fixDuplicateEntries(string $seeder, string $errorMessage): bool
    {
        $this->info("🔧 Attempting to fix duplicate entries for {$seeder}...");
        
        // Extract table name from error message
        if (preg_match('/for key \'([^.]+)\.([^\']+)\'/', $errorMessage, $matches)) {
            $tableName = $matches[1];
            
            try {
                // Clear the table data
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::table($tableName)->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                
                $this->info("🧹 Cleared duplicate data from {$tableName}");
                return true;
                
            } catch (Exception $e) {
                $this->error("Failed to clear {$tableName}: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }

    private function fixColumnNotFound(string $seeder, string $errorMessage): bool
    {
        $this->info("🔧 Column not found error detected for {$seeder}");
        $this->warn("This requires manual seeder code fixes. Please check the seeder against the migration.");
        return false;
    }

    private function fixForeignKeyConstraint(string $seeder, string $errorMessage): bool
    {
        $this->info("🔧 Foreign key constraint error detected for {$seeder}");
        $this->warn("This may require running dependent seeders first or fixing relationships.");
        return false;
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Seeding Summary:');
        $this->info('==================');
        
        $this->info("✅ Completed: " . count($this->completedSeeders));
        foreach ($this->completedSeeders as $seeder) {
            $this->line("   - {$seeder}");
        }
        
        if (!empty($this->failedSeeders)) {
            $this->newLine();
            $this->error("❌ Failed: " . count($this->failedSeeders));
            foreach ($this->failedSeeders as $failed) {
                $this->line("   - {$failed['seeder']}: {$failed['error']}");
            }
            
            $this->newLine();
            $this->warn('💡 Recommendations:');
            $this->warn('1. Check seeder code against migration schemas');
            $this->warn('2. Ensure proper seeder execution order');
            $this->warn('3. Verify foreign key relationships');
            $this->warn('4. Run: php artisan migrate:fresh before seeding');
        } else {
            $this->newLine();
            $this->info('🎉 All seeders completed successfully!');
        }
    }
}
