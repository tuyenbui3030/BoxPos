<?php

namespace Database\Seeders\Traits;

use Illuminate\Support\Facades\App;

trait EnvironmentAwareSeeding
{
    /**
     * Get environment-specific configuration
     */
    protected function getEnvironmentConfig(): array
    {
        $environment = $this->getCurrentSeedingEnvironment();
        return config("seeding.{$environment}", []);
    }

    /**
     * Get current seeding environment
     */
    protected function getCurrentSeedingEnvironment(): string
    {
        if (App::environment('testing')) {
            return 'testing';
        }
        
        if (App::environment(['local', 'development'])) {
            return 'development';
        }
        
        return 'production';
    }

    /**
     * Get environment-specific record count with fallback
     */
    protected function getEnvironmentRecordCount(string $configKey, int $defaultDev = 10, int $defaultTest = 2): int
    {
        $environment = $this->getCurrentSeedingEnvironment();
        $config = config("seeding.{$environment}", []);
        
        if (isset($config[$configKey])) {
            return (int) $config[$configKey];
        }
        
        // Fallback based on environment
        switch ($environment) {
            case 'testing':
                return $defaultTest;
            case 'development':
                return $defaultDev;
            case 'production':
                return max(1, intval($defaultDev / 20)); // Minimal for production
            default:
                return $defaultTest;
        }
    }

    /**
     * Check if should seed rich data for demos
     */
    protected function shouldSeedRichData(): bool
    {
        return $this->getCurrentSeedingEnvironment() === 'development';
    }

    /**
     * Check if should seed minimal data for tests
     */
    protected function shouldSeedMinimalData(): bool
    {
        return $this->getCurrentSeedingEnvironment() === 'testing';
    }

    /**
     * Check if should seed essential data only
     */
    protected function shouldSeedEssentialDataOnly(): bool
    {
        return $this->getCurrentSeedingEnvironment() === 'production';
    }

    /**
     * Get batch processing size based on environment
     */
    protected function getEnvironmentBatchSize(): int
    {
        $environment = $this->getCurrentSeedingEnvironment();
        
        return match ($environment) {
            'development' => config('seeding.development.batch_size', 100),
            'testing' => config('seeding.testing.batch_size', 50),
            'production' => config('seeding.production.batch_size', 25),
            default => 50
        };
    }

    /**
     * Get processing chunk size based on environment
     */
    protected function getEnvironmentChunkSize(): int
    {
        $environment = $this->getCurrentSeedingEnvironment();
        
        return match ($environment) {
            'development' => config('seeding.development.chunk_size', 500),
            'testing' => config('seeding.testing.chunk_size', 100),
            'production' => config('seeding.production.chunk_size', 50),
            default => 100
        };
    }

    /**
     * Get template data for consistent seeding
     */
    protected function getTemplateData(string $key): array
    {
        return config("seeding.templates.{$key}", []);
    }

    /**
     * Get random items from template data
     */
    protected function getRandomTemplateItems(string $key, int $count = 1): array
    {
        $items = $this->getTemplateData($key);
        
        if (empty($items)) {
            return [];
        }
        
        if ($count >= count($items)) {
            return $items;
        }
        
        return collect($items)->random($count)->toArray();
    }

    /**
     * Calculate distribution across stores
     */
    protected function calculateStoreDistribution(int $totalRecords, int $storeCount): array
    {
        if ($storeCount <= 0) {
            return [];
        }
        
        $basePerStore = intval($totalRecords / $storeCount);
        $remainder = $totalRecords % $storeCount;
        
        $distribution = array_fill(0, $storeCount, $basePerStore);
        
        // Distribute remainder
        for ($i = 0; $i < $remainder; $i++) {
            $distribution[$i]++;
        }
        
        return $distribution;
    }

    /**
     * Log environment-specific seeding information
     */
    protected function logEnvironmentSeedingInfo(string $seederName, array $additionalContext = []): void
    {
        if (method_exists($this, 'logSeedingProgress')) {
            $this->logSeedingProgress('environment_seeding_info', array_merge([
                'seeder' => $seederName,
                'environment' => $this->getCurrentSeedingEnvironment(),
                'batch_size' => $this->getEnvironmentBatchSize(),
                'chunk_size' => $this->getEnvironmentChunkSize(),
                'should_seed_rich_data' => $this->shouldSeedRichData(),
                'should_seed_minimal_data' => $this->shouldSeedMinimalData(),
                'should_seed_essential_only' => $this->shouldSeedEssentialDataOnly(),
            ], $additionalContext));
        }
    }
}