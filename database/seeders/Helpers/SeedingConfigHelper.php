<?php

namespace Database\Seeders\Helpers;

use Illuminate\Support\Facades\App;

class SeedingConfigHelper
{
    /**
     * Current environment for seeding
     */
    protected static ?string $environment = null;

    /**
     * Cached configuration
     */
    protected static ?array $config = null;

    /**
     * Get current seeding environment
     */
    public static function getCurrentEnvironment(): string
    {
        if (static::$environment === null) {
            static::$environment = static::detectEnvironment();
        }

        return static::$environment;
    }

    /**
     * Detect current environment for seeding purposes
     */
    protected static function detectEnvironment(): string
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
     * Get full seeding configuration
     */
    public static function getConfig(): array
    {
        if (static::$config === null) {
            static::$config = config('seeding', []);
        }

        return static::$config;
    }

    /**
     * Get environment-specific configuration
     */
    public static function getEnvironmentConfig(?string $environment = null): array
    {
        $env = $environment ?? static::getCurrentEnvironment();
        return data_get(static::getConfig(), $env, []);
    }

    /**
     * Get configuration value for current environment
     */
    public static function get(string $key, $default = null)
    {
        $envConfig = static::getEnvironmentConfig();
        return data_get($envConfig, $key, $default);
    }

    /**
     * Get template data
     */
    public static function getTemplate(string $key): array
    {
        return data_get(static::getConfig(), "templates.{$key}", []);
    }

    /**
     * Get execution order configuration
     */
    public static function getExecutionOrder(): array
    {
        return data_get(static::getConfig(), 'execution_order', []);
    }

    /**
     * Get validation rules
     */
    public static function getValidationRules(): array
    {
        return data_get(static::getConfig(), 'validation', []);
    }

    /**
     * Check if current environment allows seeding
     */
    public static function canSeed(): bool
    {
        $allowedEnvironments = ['local', 'development', 'testing', 'staging'];
        return in_array(App::environment(), $allowedEnvironments);
    }

    /**
     * Get record count for specific configuration key
     */
    public static function getRecordCount(string $key, int $fallbackDev = 10, int $fallbackTest = 2): int
    {
        $value = static::get($key);
        
        if ($value !== null) {
            return (int) $value;
        }

        // Environment-based fallback
        $environment = static::getCurrentEnvironment();
        
        return match ($environment) {
            'development' => $fallbackDev,
            'testing' => $fallbackTest,
            'production' => max(1, intval($fallbackDev / 20)),
            default => $fallbackTest
        };
    }

    /**
     * Get batch size for current environment
     */
    public static function getBatchSize(): int
    {
        return static::get('batch_size', 50);
    }

    /**
     * Get chunk size for current environment
     */
    public static function getChunkSize(): int
    {
        return static::get('chunk_size', 100);
    }

    /**
     * Check if should create rich demo data
     */
    public static function shouldCreateRichData(): bool
    {
        return static::getCurrentEnvironment() === 'development';
    }

    /**
     * Check if should create minimal test data
     */
    public static function shouldCreateMinimalData(): bool
    {
        return static::getCurrentEnvironment() === 'testing';
    }

    /**
     * Check if should create essential data only
     */
    public static function shouldCreateEssentialDataOnly(): bool
    {
        return static::getCurrentEnvironment() === 'production';
    }

    /**
     * Get environment multiplier for scaling data
     */
    public static function getEnvironmentMultiplier(): float
    {
        return match (static::getCurrentEnvironment()) {
            'development' => 1.0,
            'testing' => 0.1,
            'production' => 0.05,
            default => 0.1
        };
    }

    /**
     * Calculate records per store
     */
    public static function getRecordsPerStore(int $baseCount, int $storeCount): int
    {
        if ($storeCount <= 0) {
            return 0;
        }

        $multiplier = static::getEnvironmentMultiplier();
        return max(1, intval(($baseCount * $multiplier) / $storeCount));
    }

    /**
     * Get random template items
     */
    public static function getRandomTemplateItems(string $templateKey, int $count = 1): array
    {
        $items = static::getTemplate($templateKey);
        
        if (empty($items) || $count <= 0) {
            return [];
        }
        
        if ($count >= count($items)) {
            return $items;
        }
        
        return collect($items)->random($count)->toArray();
    }

    /**
     * Validate seeding requirements
     */
    public static function validateRequirements(int $storeCount): void
    {
        $rules = static::getValidationRules();
        
        $minStores = data_get($rules, 'required_stores_minimum', 1);
        if ($storeCount < $minStores) {
            throw new \Exception("Minimum {$minStores} stores required for seeding, got {$storeCount}");
        }
    }

    /**
     * Reset cached values (useful for testing)
     */
    public static function reset(): void
    {
        static::$environment = null;
        static::$config = null;
    }

    /**
     * Force set environment (useful for testing)
     */
    public static function setEnvironment(string $environment): void
    {
        static::$environment = $environment;
    }
}