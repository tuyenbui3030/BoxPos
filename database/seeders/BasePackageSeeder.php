<?php

namespace Database\Seeders;

use Database\Seeders\Helpers\SeedingConfigHelper;
use Database\Seeders\Traits\EnvironmentAwareSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Log\Traits\Loggable;
use Packages\Store\Models\Store;

abstract class BasePackageSeeder extends Seeder
{
    use Loggable, EnvironmentAwareSeeding;

    /**
     * Cached stores collection
     */
    protected Collection $stores;

    /**
     * Environment detection
     */
    protected bool $isDevelopment;
    protected bool $isTesting;

    /**
     * Seeding configuration
     */
    protected array $config;

    public function __construct()
    {
        $this->isDevelopment = app()->environment('local', 'development');
        $this->isTesting = app()->environment('testing');
        $this->config = SeedingConfigHelper::getConfig();
        $this->stores = collect();
    }

    /**
     * Abstract method that must be implemented by package seeders
     */
    abstract public function run(): void;

    /**
     * Get all active stores
     */
    protected function getStores(): Collection
    {
        if ($this->stores->isEmpty()) {
            $this->stores = Store::active()->get();
            
            if ($this->stores->isEmpty()) {
                $this->logError(new \Exception('No active stores found for seeding'), [
                    'seeder' => static::class
                ]);
                throw new \Exception('No active stores found. Please run StoreSeeder first.');
            }
        }

        return $this->stores;
    }

    /**
     * Get a random store from available stores
     */
    protected function getRandomStore(): Store
    {
        $stores = $this->getStores();
        return $stores->random();
    }

    /**
     * Execute callback for all stores
     */
    protected function seedForAllStores(callable $callback): void
    {
        $stores = $this->getStores();
        
        foreach ($stores as $store) {
            $this->logSeedingProgress('seeding_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'seeder' => static::class
            ]);

            $callback($store);
        }
    }

    /**
     * Log seeding progress with context
     */
    protected function logSeedingProgress(string $message, array $context = []): void
    {
        $this->logActivity($message, array_merge($context, [
            'seeder_class' => static::class,
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString()
        ]));
    }

    /**
     * Get record count based on environment
     */
    protected function getRecordCount(int $development, ?int $testing = null): int
    {
        if ($this->isTesting) {
            return $testing ?? max(1, intval($development / 10));
        }
        
        if ($this->isDevelopment) {
            return $development;
        }
        
        // Production environment - minimal data
        return $testing ?? max(1, intval($development / 20));
    }

    /**
     * Get record count from configuration for specific key
     */
    protected function getConfigRecordCount(string $configKey, int $fallbackDevelopment = 10, ?int $fallbackTesting = null): int
    {
        $configValue = $this->getConfigValue($configKey);
        
        if ($configValue !== null) {
            return (int) $configValue;
        }
        
        return $this->getRecordCount($fallbackDevelopment, $fallbackTesting);
    }

    /**
     * Get environment-specific multiplier for data generation
     */
    protected function getEnvironmentMultiplier(): float
    {
        if ($this->isTesting) {
            return 0.1; // 10% of development data
        }
        
        if ($this->isDevelopment) {
            return 1.0; // Full development data
        }
        
        return 0.05; // 5% for production (minimal)
    }

    /**
     * Calculate records per store based on environment
     */
    protected function getRecordsPerStore(int $baseCount): int
    {
        $storeCount = $this->getStores()->count();
        $multiplier = $this->getEnvironmentMultiplier();
        
        return max(1, intval(($baseCount * $multiplier) / $storeCount));
    }

    /**
     * Get configuration value for current environment
     */
    protected function getConfigValue(string $key, $default = null)
    {
        $environment = $this->isTesting ? 'testing' : ($this->isDevelopment ? 'development' : 'production');
        
        return data_get($this->config, "{$environment}.{$key}", $default);
    }

    /**
     * Execute seeding with transaction and error handling
     */
    protected function executeWithTransaction(callable $callback): void
    {
        $this->logSeedingProgress('seeding_started');

        DB::beginTransaction();

        try {
            $startTime = microtime(true);
            
            $callback();
            
            DB::commit();
            
            $this->logOperationPerformance(
                'seeding_completed',
                $startTime,
                ['seeder' => static::class]
            );
            
        } catch (\Exception $e) {
            DB::rollback();
            
            $this->logError($e, [
                'seeder' => static::class,
                'operation' => 'seeding'
            ]);
            
            throw $e;
        }
    }

    /**
     * Check if we should seed data for store isolation
     */
    protected function shouldSeedForStore(): bool
    {
        return !in_array(static::class, $this->getSystemSeeders());
    }

    /**
     * Get list of system seeders that don't need store isolation
     */
    protected function getSystemSeeders(): array
    {
        return [
            'Database\Seeders\UserSeeder',
            'Packages\User\Database\Seeders\UserSeeder',
        ];
    }

    /**
     * Validate store exists before seeding business data
     */
    protected function validateStoreExists(int $storeId): void
    {
        if (!Store::where('id', $storeId)->exists()) {
            throw new \Exception("Store with ID {$storeId} does not exist");
        }
    }

    /**
     * Get seeding batch size based on environment
     */
    protected function getBatchSize(): int
    {
        return $this->getConfigValue('batch_size', $this->isDevelopment ? 100 : 50);
    }

    /**
     * Check if current environment allows seeding
     */
    protected function canSeed(): bool
    {
        $allowedEnvironments = ['local', 'development', 'testing', 'staging'];
        return in_array(app()->environment(), $allowedEnvironments);
    }

    /**
     * Ensure seeding is allowed in current environment
     */
    protected function ensureSeedingAllowed(): void
    {
        if (!$this->canSeed()) {
            throw new \Exception(
                'Seeding is not allowed in ' . app()->environment() . ' environment'
            );
        }
    }

    /**
     * Create realistic Vietnamese data for testing
     */
    protected function getVietnameseNames(): array
    {
        return [
            'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Văn Cường', 'Phạm Thị Dung',
            'Hoàng Văn Em', 'Vũ Thị Phương', 'Đặng Văn Giang', 'Bùi Thị Hoa',
            'Dương Văn Inh', 'Ngô Thị Kim', 'Lý Văn Long', 'Tôn Thị Mai',
            'Đinh Văn Nam', 'Chu Thị Oanh', 'Võ Văn Phúc', 'Đỗ Thị Quỳnh'
        ];
    }

    /**
     * Get Vietnamese company names for suppliers/customers
     */
    protected function getVietnameseCompanyNames(): array
    {
        return [
            'Công ty TNHH Xây dựng Hòa Bình',
            'Công ty CP Vật liệu Xây dựng Việt Nam',
            'Công ty TNHH Thương mại Đại Phát',
            'Công ty CP Xi măng Hà Tiên',
            'Công ty TNHH Sắt thép Hoa Sen',
            'Công ty CP Gạch Đồng Tâm',
            'Công ty TNHH Cát đá Minh Phú',
            'Công ty CP Vật tư Xây dựng Sài Gòn'
        ];
    }

    /**
     * Get Vietnamese addresses
     */
    protected function getVietnameseAddresses(): array
    {
        return [
            '123 Nguyễn Huệ, Quận 1, TP.HCM',
            '456 Lê Lợi, Quận 3, TP.HCM',
            '789 Trần Hưng Đạo, Quận 5, TP.HCM',
            '321 Võ Văn Tần, Quận 3, TP.HCM',
            '654 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
            '987 Cách Mạng Tháng 8, Quận 10, TP.HCM',
            '147 Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
            '258 Lý Tự Trọng, Quận 1, TP.HCM'
        ];
    }

    /**
     * Get current environment name
     */
    protected function getCurrentEnvironment(): string
    {
        return app()->environment();
    }

    /**
     * Check if running in development environment
     */
    protected function isDevelopmentEnvironment(): bool
    {
        return $this->isDevelopment;
    }

    /**
     * Check if running in testing environment
     */
    protected function isTestingEnvironment(): bool
    {
        return $this->isTesting;
    }

    /**
     * Check if running in production environment
     */
    protected function isProductionEnvironment(): bool
    {
        return app()->environment('production');
    }

    /**
     * Get template data from configuration
     */
    protected function getTemplateData(string $templateKey): array
    {
        return data_get($this->config, "templates.{$templateKey}", []);
    }

    /**
     * Get execution order for seeders
     */
    protected function getExecutionOrder(): array
    {
        return data_get($this->config, 'execution_order', []);
    }

    /**
     * Get validation rules from configuration
     */
    protected function getValidationRules(): array
    {
        return data_get($this->config, 'validation', []);
    }

    /**
     * Validate minimum requirements are met
     */
    protected function validateMinimumRequirements(): void
    {
        $rules = $this->getValidationRules();
        
        // Validate minimum stores
        $minStores = data_get($rules, 'required_stores_minimum', 1);
        if ($this->getStores()->count() < $minStores) {
            throw new \Exception("Minimum {$minStores} stores required for seeding");
        }

        $this->logSeedingProgress('minimum_requirements_validated', [
            'stores_count' => $this->getStores()->count(),
            'min_required' => $minStores
        ]);
    }

    /**
     * Get environment-specific seeding strategy
     */
    protected function getSeedingStrategy(): string
    {
        if ($this->isTesting) {
            return 'minimal'; // Minimal data for fast tests
        }
        
        if ($this->isDevelopment) {
            return 'rich'; // Rich data for demonstration
        }
        
        return 'essential'; // Essential data only for production
    }

    /**
     * Should create sample business data based on environment
     */
    protected function shouldCreateSampleData(): bool
    {
        return $this->isDevelopment || $this->isTesting;
    }

    /**
     * Should create rich demo data
     */
    protected function shouldCreateRichData(): bool
    {
        return $this->isDevelopment;
    }

    /**
     * Get chunk size for bulk operations based on environment
     */
    protected function getChunkSize(): int
    {
        return $this->getConfigValue('chunk_size', $this->isDevelopment ? 500 : 100);
    }

    /**
     * Log environment information at start of seeding
     */
    protected function logEnvironmentInfo(): void
    {
        $this->logSeedingProgress('environment_info', [
            'environment' => $this->getCurrentEnvironment(),
            'strategy' => $this->getSeedingStrategy(),
            'stores_count' => $this->getStores()->count(),
            'batch_size' => $this->getBatchSize(),
            'chunk_size' => $this->getChunkSize(),
            'should_create_sample_data' => $this->shouldCreateSampleData(),
            'should_create_rich_data' => $this->shouldCreateRichData()
        ]);
    }

    /**
     * Initialize seeding with environment validation and logging
     */
    protected function initializeSeeding(): void
    {
        // Ensure seeding is allowed
        $this->ensureSeedingAllowed();
        
        // Validate minimum requirements
        $this->validateMinimumRequirements();
        
        // Log environment information
        $this->logEnvironmentInfo();
        
        // Log using the trait method as well
        $this->logEnvironmentSeedingInfo(static::class);
    }

    /**
     * Get seeding configuration using helper
     */
    protected function getSeedingConfig(): array
    {
        return SeedingConfigHelper::getEnvironmentConfig();
    }

    /**
     * Get configuration value using helper with fallback
     */
    protected function getConfigValueWithHelper(string $key, $default = null)
    {
        return SeedingConfigHelper::get($key, $default);
    }

    /**
     * Get record count using configuration helper
     */
    protected function getRecordCountFromConfig(string $configKey, int $fallbackDev = 10, int $fallbackTest = 2): int
    {
        return SeedingConfigHelper::getRecordCount($configKey, $fallbackDev, $fallbackTest);
    }

    /**
     * Get template data using helper
     */
    protected function getTemplateDataFromConfig(string $templateKey): array
    {
        return SeedingConfigHelper::getTemplate($templateKey);
    }

    /**
     * Get random template items using helper
     */
    protected function getRandomTemplateItemsFromConfig(string $templateKey, int $count = 1): array
    {
        return SeedingConfigHelper::getRandomTemplateItems($templateKey, $count);
    }

    /**
     * Calculate optimal distribution of records across stores
     */
    protected function calculateOptimalStoreDistribution(int $totalRecords): array
    {
        $stores = $this->getStores();
        $storeCount = $stores->count();
        
        return $this->calculateStoreDistribution($totalRecords, $storeCount);
    }

    /**
     * Execute seeding with full environment setup
     */
    protected function executeEnvironmentAwareSeeding(callable $callback): void
    {
        $this->initializeSeeding();
        $this->executeWithTransaction($callback);
    }
}