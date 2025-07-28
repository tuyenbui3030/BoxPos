<?php

namespace App\Console\Commands;

use Database\Seeders\Helpers\SeedingConfigHelper;
use Illuminate\Console\Command;

class TestSeedingEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seeding:test-environment {--environment= : Force specific environment}';

    /**
     * The console command description.
     */
    protected $description = 'Test seeding environment configuration and display current settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Seeding Environment Configuration');
        $this->line('');

        // Force environment if provided
        if ($environment = $this->option('environment')) {
            SeedingConfigHelper::setEnvironment($environment);
            $this->warn("Forced environment to: {$environment}");
        }

        // Display current environment info
        $this->displayEnvironmentInfo();
        
        // Display configuration values
        $this->displayConfigurationValues();
        
        // Display template data
        $this->displayTemplateData();
        
        // Display validation rules
        $this->displayValidationRules();

        $this->line('');
        $this->info('Environment configuration test completed!');

        return 0;
    }

    private function displayEnvironmentInfo(): void
    {
        $this->line('<fg=cyan>Environment Information:</>');
        $this->table(
            ['Property', 'Value'],
            [
                ['Current Environment', SeedingConfigHelper::getCurrentEnvironment()],
                ['Laravel Environment', app()->environment()],
                ['Can Seed', SeedingConfigHelper::canSeed() ? 'Yes' : 'No'],
                ['Should Create Rich Data', SeedingConfigHelper::shouldCreateRichData() ? 'Yes' : 'No'],
                ['Should Create Minimal Data', SeedingConfigHelper::shouldCreateMinimalData() ? 'Yes' : 'No'],
                ['Should Create Essential Only', SeedingConfigHelper::shouldCreateEssentialDataOnly() ? 'Yes' : 'No'],
                ['Environment Multiplier', SeedingConfigHelper::getEnvironmentMultiplier()],
                ['Batch Size', SeedingConfigHelper::getBatchSize()],
                ['Chunk Size', SeedingConfigHelper::getChunkSize()],
            ]
        );
    }

    private function displayConfigurationValues(): void
    {
        $this->line('<fg=cyan>Configuration Values:</>');
        
        $configKeys = [
            'stores_count',
            'customers_per_store',
            'materials_per_category',
            'employees_per_store',
            'orders_per_customer',
            'inventory_items_per_store',
            'cash_transactions_per_account',
            'promotions_per_store'
        ];

        $rows = [];
        foreach ($configKeys as $key) {
            $value = SeedingConfigHelper::getRecordCount($key, 10, 2);
            $rows[] = [$key, $value];
        }

        $this->table(['Configuration Key', 'Value'], $rows);
    }

    private function displayTemplateData(): void
    {
        $this->line('<fg=cyan>Template Data:</>');
        
        $templateKeys = [
            'material_categories',
            'material_units',
            'departments',
            'cash_categories',
            'payment_methods',
            'customer_types'
        ];

        $rows = [];
        foreach ($templateKeys as $key) {
            $data = SeedingConfigHelper::getTemplate($key);
            $count = count($data);
            $sample = $count > 0 ? implode(', ', array_slice($data, 0, 3)) : 'None';
            if ($count > 3) {
                $sample .= '...';
            }
            $rows[] = [$key, $count, $sample];
        }

        $this->table(['Template Key', 'Count', 'Sample Items'], $rows);
    }

    private function displayValidationRules(): void
    {
        $this->line('<fg=cyan>Validation Rules:</>');
        
        $rules = SeedingConfigHelper::getValidationRules();
        
        if (empty($rules)) {
            $this->line('No validation rules configured.');
            return;
        }

        $rows = [];
        foreach ($rules as $key => $value) {
            $rows[] = [$key, $value];
        }

        $this->table(['Rule', 'Value'], $rows);
    }
}