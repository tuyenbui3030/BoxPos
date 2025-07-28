<?php

namespace App\Console\Commands;

use Database\Seeders\SeedingHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'boxpos:seed 
                            {--clean : Clean existing data before seeding}
                            {--validate : Only validate seeding environment}
                            {--stats : Show seeding statistics}
                            {--class= : Run specific seeder class}';

    /**
     * The console command description.
     */
    protected $description = 'BoxPos seeding management command with validation and statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            if ($this->option('validate')) {
                return $this->validateEnvironment();
            }

            if ($this->option('stats')) {
                return $this->showStatistics();
            }

            if ($this->option('clean')) {
                return $this->cleanAndSeed();
            }

            if ($this->option('class')) {
                return $this->runSpecificSeeder();
            }

            return $this->runFullSeeding();

        } catch (\Exception $e) {
            $this->error('❌ Command failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Validate seeding environment
     */
    private function validateEnvironment(): int
    {
        $this->info('🔍 Validating seeding environment...');

        try {
            SeedingHelper::validateSeedingEnvironment();
            $this->info('✅ Environment validation passed');

            $stores = SeedingHelper::getValidatedStores();
            $this->info("✅ Found {$stores->count()} active store(s)");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Environment validation failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show seeding statistics
     */
    private function showStatistics(): int
    {
        $this->info('📊 Current Database Statistics:');
        $this->info('');

        $stats = SeedingHelper::getSeedingStatistics();

        if (empty($stats)) {
            $this->warn('No data found in database');
            return 0;
        }

        $this->table(
            ['Table', 'Record Count'],
            collect($stats)->map(fn($count, $table) => [$table, number_format($count)])->toArray()
        );

        // Check data consistency
        $issues = SeedingHelper::validateDataConsistency();
        
        if (!empty($issues)) {
            $this->warn('');
            $this->warn('⚠️  Data Consistency Issues:');
            foreach ($issues as $issue) {
                $this->warn('   - ' . $issue);
            }
        } else {
            $this->info('');
            $this->info('✅ Data consistency validation passed');
        }

        return 0;
    }

    /**
     * Clean existing data and run seeding
     */
    private function cleanAndSeed(): int
    {
        if (!$this->confirm('⚠️  This will delete ALL existing data. Are you sure?')) {
            $this->info('Operation cancelled');
            return 0;
        }

        $this->info('🧹 Cleaning existing data...');
        SeedingHelper::cleanupSeedData();
        $this->info('✅ Data cleanup completed');

        return $this->runFullSeeding();
    }

    /**
     * Run specific seeder class
     */
    private function runSpecificSeeder(): int
    {
        $class = $this->option('class');
        
        $this->info("🚀 Running seeder: {$class}");
        
        Artisan::call('db:seed', ['--class' => $class]);
        
        $this->info('✅ Seeder completed');
        
        return 0;
    }

    /**
     * Run full database seeding
     */
    private function runFullSeeding(): int
    {
        $this->info('🚀 Starting full database seeding...');
        
        Artisan::call('db:seed');
        
        $this->info('✅ Full seeding completed');
        
        return 0;
    }
}