<?php

namespace Database\Seeders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Store\Models\Store;

class SeedingHelper
{
    /**
     * Validate seeding environment and prerequisites
     */
    public static function validateSeedingEnvironment(): void
    {
        $allowedEnvironments = ['local', 'development', 'testing', 'staging'];
        
        if (!in_array(app()->environment(), $allowedEnvironments)) {
            throw new \Exception(
                'Seeding is not allowed in ' . app()->environment() . ' environment'
            );
        }

        // Check if required tables exist
        $requiredTables = ['stores', 'users', 'user_stores'];
        foreach ($requiredTables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                throw new \Exception("Required table '{$table}' does not exist. Please run migrations first.");
            }
        }
    }

    /**
     * Get stores with validation
     */
    public static function getValidatedStores(): Collection
    {
        $stores = Store::active()->get();
        
        if ($stores->isEmpty()) {
            throw new \Exception('No active stores found. Please run StoreSeeder first.');
        }

        $minStores = config('seeding.validation.required_stores_minimum', 1);
        if ($stores->count() < $minStores) {
            throw new \Exception("At least {$minStores} active store(s) required for seeding.");
        }

        return $stores;
    }

    /**
     * Distribute items evenly across stores
     */
    public static function distributeAcrossStores(Collection $stores, int $totalItems): array
    {
        $storeCount = $stores->count();
        $itemsPerStore = intval($totalItems / $storeCount);
        $remainder = $totalItems % $storeCount;
        
        $distribution = [];
        
        foreach ($stores as $index => $store) {
            $count = $itemsPerStore;
            
            // Distribute remainder to first stores
            if ($index < $remainder) {
                $count++;
            }
            
            $distribution[$store->id] = $count;
        }
        
        return $distribution;
    }

    /**
     * Create realistic Vietnamese phone numbers
     */
    public static function generateVietnamesePhone(): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '088', '089'];
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        
        return $prefix . $suffix;
    }

    /**
     * Generate Vietnamese business registration number
     */
    public static function generateBusinessRegistration(): string
    {
        return rand(1000000000, 9999999999);
    }

    /**
     * Generate Vietnamese tax code
     */
    public static function generateTaxCode(): string
    {
        return rand(100000000, 999999999);
    }

    /**
     * Get random Vietnamese city/province
     */
    public static function getRandomVietnameseCity(): string
    {
        $cities = [
            'TP. Hồ Chí Minh', 'Hà Nội', 'Đà Nẵng', 'Hải Phòng', 'Cần Thơ',
            'Biên Hòa', 'Nha Trang', 'Huế', 'Buôn Ma Thuột', 'Vũng Tàu',
            'Quy Nhon', 'Nam Định', 'Long Xuyên', 'Thái Nguyên', 'Phan Thiết'
        ];
        
        return $cities[array_rand($cities)];
    }

    /**
     * Generate realistic Vietnamese email
     */
    public static function generateVietnameseEmail(string $name): string
    {
        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $cleanName = self::removeVietnameseAccents(strtolower($name));
        $cleanName = preg_replace('/[^a-z0-9]/', '', $cleanName);
        
        return $cleanName . rand(1, 999) . '@' . $domains[array_rand($domains)];
    }

    /**
     * Remove Vietnamese accents from string
     */
    public static function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        
        $replacements = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        return str_replace($accents, $replacements, $str);
    }

    /**
     * Generate realistic price with Vietnamese currency formatting
     */
    public static function generatePrice(int $min = 10000, int $max = 1000000): int
    {
        // Generate price in multiples of 1000 VND
        $price = rand($min / 1000, $max / 1000) * 1000;
        return $price;
    }

    /**
     * Generate realistic quantity for inventory
     */
    public static function generateQuantity(string $unit): int
    {
        $quantityRanges = [
            'kg' => [10, 1000],
            'm3' => [1, 100],
            'cái' => [1, 500],
            'bao' => [1, 200],
            'tấn' => [1, 50],
            'm2' => [10, 1000],
            'mét' => [10, 500],
            'lít' => [1, 200],
            'thùng' => [1, 100],
            'cuộn' => [1, 50],
            'tấm' => [1, 200],
            'viên' => [100, 10000],
            'bộ' => [1, 100],
            'chiếc' => [1, 200],
            'gói' => [1, 500],
        ];
        
        $range = $quantityRanges[$unit] ?? [1, 100];
        return rand($range[0], $range[1]);
    }

    /**
     * Validate data consistency after seeding
     */
    public static function validateDataConsistency(): array
    {
        $issues = [];
        
        // Check store isolation
        $businessTables = [
            'customers', 'material_inventory', 'sales_orders', 'employees',
            'cash_accounts', 'promotions'
        ];
        
        foreach ($businessTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $recordsWithoutStore = DB::table($table)
                    ->whereNull('store_id')
                    ->count();
                    
                if ($recordsWithoutStore > 0) {
                    $issues[] = "Table '{$table}' has {$recordsWithoutStore} records without store_id";
                }
            }
        }
        
        // Check user-store relationships
        $usersWithoutStores = DB::table('users')
            ->leftJoin('user_stores', 'users.id', '=', 'user_stores.user_id')
            ->whereNull('user_stores.user_id')
            ->count();
            
        if ($usersWithoutStores > 0) {
            $issues[] = "{$usersWithoutStores} users are not assigned to any store";
        }
        
        return $issues;
    }

    /**
     * Clean up seeded data (for testing purposes)
     */
    public static function cleanupSeedData(): void
    {
        if (!app()->environment(['testing', 'local'])) {
            throw new \Exception('Data cleanup is only allowed in testing or local environment');
        }

        $tables = [
            'notifications', 'loyalty_transactions', 'loyalty_memberships', 'loyalty_programs',
            'promotion_usages', 'promotions', 'payments', 'invoice_items', 'invoices',
            'sales_order_items', 'sales_orders', 'cash_transactions', 'cash_accounts',
            'employee_timesheets', 'employee_schedules', 'employees', 'departments',
            'material_pricing', 'inventory_movements', 'material_inventory',
            'material_suppliers', 'building_materials', 'material_units', 'material_categories',
            'product_variants', 'products', 'product_categories', 'customers',
            'user_stores', 'stores', 'users'
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Get seeding statistics
     */
    public static function getSeedingStatistics(): array
    {
        $stats = [];
        
        $tables = [
            'stores', 'users', 'customers', 'material_categories', 'building_materials',
            'material_suppliers', 'material_inventory', 'sales_orders', 'employees',
            'cash_accounts', 'promotions', 'loyalty_programs'
        ];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $stats[$table] = DB::table($table)->count();
            }
        }
        
        return $stats;
    }
}