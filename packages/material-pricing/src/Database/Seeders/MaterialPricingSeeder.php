<?php

namespace Packages\MaterialPricing\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\MaterialPricing\Models\MaterialPricing;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class MaterialPricingSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedForAllStores(function (Store $store) {
                $this->createPricingForStore($store);
            });
        });
    }

    /**
     * Create pricing records for a specific store.
     */
    private function createPricingForStore(Store $store): void
    {
        $this->validateStoreExists($store->id);
        
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        
        if ($materials->isEmpty()) {
            $this->logSeedingProgress('no_materials_found_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);
            return;
        }

        $users = User::whereHas('stores', function($q) use ($store) {
            $q->where('store_id', $store->id);
        })->get();

        if ($users->isEmpty()) {
            $this->logSeedingProgress('no_users_found_for_store', [
                'store_id' => $store->id,
                'store_name' => $store->name
            ]);
            return;
        }

        $customerTypes = [
            MaterialPricing::CUSTOMER_RETAIL,
            MaterialPricing::CUSTOMER_WHOLESALE,
            MaterialPricing::CUSTOMER_CONTRACTOR,
            MaterialPricing::CUSTOMER_VIP,
            MaterialPricing::CUSTOMER_STAFF
        ];

        $materialCount = $materials->count();
        $this->logSeedingProgress('creating_pricing_for_materials', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'material_count' => $materialCount
        ]);

        foreach ($materials as $material) {
            $this->createPricingHistoryForMaterial($material, $customerTypes, $users);
        }
    }

    /**
     * Create pricing history for a specific material.
     */
    private function createPricingHistoryForMaterial(BuildingMaterial $material, array $customerTypes, $users): void
    {
        $basePrices = $this->getBasePricesForMaterial($material);
        $priceHistoryCount = $this->getRecordCount(6, 3); // 6 months for dev, 3 for testing

        foreach ($customerTypes as $customerType) {
            $this->createPricingHistoryForCustomerType($material, $customerType, $basePrices, $users, $priceHistoryCount);
        }
    }

    /**
     * Get base prices for material type.
     */
    private function getBasePricesForMaterial(BuildingMaterial $material): array
    {
        $materialName = strtolower($material->name);
        
        if (str_contains($materialName, 'xi măng')) {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 115000,
                MaterialPricing::CUSTOMER_WHOLESALE => 105000,
                MaterialPricing::CUSTOMER_CONTRACTOR => 100000,
                MaterialPricing::CUSTOMER_VIP => 95000,
                MaterialPricing::CUSTOMER_STAFF => 90000,
            ];
        } elseif (str_contains($materialName, 'thép')) {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 18500000,
                MaterialPricing::CUSTOMER_WHOLESALE => 17500000,
                MaterialPricing::CUSTOMER_CONTRACTOR => 17000000,
                MaterialPricing::CUSTOMER_VIP => 16500000,
                MaterialPricing::CUSTOMER_STAFF => 16000000,
            ];
        } elseif (str_contains($materialName, 'gạch')) {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 600,
                MaterialPricing::CUSTOMER_WHOLESALE => 500,
                MaterialPricing::CUSTOMER_CONTRACTOR => 450,
                MaterialPricing::CUSTOMER_VIP => 420,
                MaterialPricing::CUSTOMER_STAFF => 400,
            ];
        } elseif (str_contains($materialName, 'cát')) {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 450000,
                MaterialPricing::CUSTOMER_WHOLESALE => 400000,
                MaterialPricing::CUSTOMER_CONTRACTOR => 380000,
                MaterialPricing::CUSTOMER_VIP => 360000,
                MaterialPricing::CUSTOMER_STAFF => 350000,
            ];
        } elseif (str_contains($materialName, 'sơn')) {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 280000,
                MaterialPricing::CUSTOMER_WHOLESALE => 250000,
                MaterialPricing::CUSTOMER_CONTRACTOR => 230000,
                MaterialPricing::CUSTOMER_VIP => 210000,
                MaterialPricing::CUSTOMER_STAFF => 200000,
            ];
        } else {
            return [
                MaterialPricing::CUSTOMER_RETAIL => 100000,
                MaterialPricing::CUSTOMER_WHOLESALE => 90000,
                MaterialPricing::CUSTOMER_CONTRACTOR => 85000,
                MaterialPricing::CUSTOMER_VIP => 80000,
                MaterialPricing::CUSTOMER_STAFF => 75000,
            ];
        }
    }

    /**
     * Create pricing history for a customer type with time trends.
     */
    private function createPricingHistoryForCustomerType(BuildingMaterial $material, string $customerType, array $basePrices, $users, int $historyCount): void
    {
        $basePrice = $basePrices[$customerType];
        $user = $users->random();
        
        // Create pricing history with realistic price trends
        $priceHistory = $this->generatePriceTrends($basePrice, $historyCount);
        
        foreach ($priceHistory as $index => $priceData) {
            $this->createQuantityTiersForPeriod($material, $customerType, $priceData, $user, $index);
        }
        
        // Create seasonal pricing if applicable
        if ($this->shouldCreateSeasonalPricing($material)) {
            $this->createSeasonalPricing($material, $customerType, $basePrice, $user);
        }
    }

    /**
     * Generate realistic price trends over time.
     */
    private function generatePriceTrends(float $basePrice, int $periods): array
    {
        $trends = [];
        $currentPrice = $basePrice;
        $startDate = now()->subMonths($periods);
        
        for ($i = 0; $i < $periods; $i++) {
            $effectiveFrom = $startDate->copy()->addMonths($i);
            $effectiveTo = $i === $periods - 1 ? null : $startDate->copy()->addMonths($i + 1)->subDay();
            
            // Apply realistic price fluctuations (±5% to ±15%)
            $fluctuation = $this->getPriceFluctuation($i, $periods);
            $currentPrice = $basePrice * (1 + $fluctuation);
            
            $trends[] = [
                'price' => round($currentPrice, -2), // Round to nearest 100
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'is_active' => $i === $periods - 1, // Only latest is active
                'period_index' => $i
            ];
        }
        
        return $trends;
    }

    /**
     * Get price fluctuation based on period and market trends.
     */
    private function getPriceFluctuation(int $periodIndex, int $totalPeriods): float
    {
        // Simulate market trends: gradual increase over time with some volatility
        $baseIncrease = ($periodIndex / $totalPeriods) * 0.1; // 10% increase over full period
        $volatility = (rand(-10, 10) / 100); // ±10% random volatility
        
        return $baseIncrease + $volatility;
    }

    /**
     * Create quantity-based pricing tiers for a specific time period.
     */
    private function createQuantityTiersForPeriod(BuildingMaterial $material, string $customerType, array $priceData, User $user, int $periodIndex): void
    {
        $basePrice = $priceData['price'];
        $effectiveFrom = $priceData['effective_from'];
        $effectiveTo = $priceData['effective_to'];
        $isActive = $priceData['is_active'];
        
        // Tier 1: Small quantity (1-10 units)
        MaterialPricing::create([
            'store_id' => $material->store_id,
            'material_id' => $material->id,
            'price_list_name' => ucfirst($customerType) . ' - Lẻ',
            'customer_type' => $customerType,
            'min_quantity' => 1,
            'max_quantity' => 10,
            'unit_price' => $basePrice,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'season' => MaterialPricing::SEASON_ALL_YEAR,
            'delivery_areas' => $this->getDeliveryAreas(),
            'delivery_surcharge' => $customerType === MaterialPricing::CUSTOMER_RETAIL ? 50000 : 0,
            'free_delivery' => false,
            'free_delivery_threshold' => $this->getFreeDeliveryThreshold($customerType),
            'currency' => 'VND',
            'tax_rate' => 10.0,
            'tax_inclusive' => false,
            'is_active' => $isActive,
            'is_default' => $customerType === MaterialPricing::CUSTOMER_RETAIL && $isActive,
            'priority' => 1,
            'notes' => "Giá {$customerType} cho số lượng nhỏ - Kỳ " . ($periodIndex + 1),
            'conditions' => $this->getPricingConditions($customerType),
            'created_by' => $user->id,
        ]);

        // Tier 2: Medium quantity (11-100 units) with discount
        if ($this->shouldCreateMediumTier($material)) {
            MaterialPricing::create([
                'store_id' => $material->store_id,
                'material_id' => $material->id,
                'price_list_name' => ucfirst($customerType) . ' - Sỉ',
                'customer_type' => $customerType,
                'min_quantity' => 11,
                'max_quantity' => 100,
                'unit_price' => $basePrice,
                'discount_percentage' => $this->getDiscountPercentage($customerType, 'medium'),
                'discount_amount' => 0,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'season' => MaterialPricing::SEASON_ALL_YEAR,
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => true,
                'free_delivery_threshold' => null,
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => $isActive,
                'is_default' => false,
                'priority' => 2,
                'notes' => "Giá {$customerType} cho số lượng trung bình - Kỳ " . ($periodIndex + 1),
                'conditions' => $this->getPricingConditions($customerType),
                'created_by' => $user->id,
            ]);
        }

        // Tier 3: Large quantity (100+ units) with bigger discount
        if ($this->shouldCreateLargeTier($material)) {
            MaterialPricing::create([
                'store_id' => $material->store_id,
                'material_id' => $material->id,
                'price_list_name' => ucfirst($customerType) . ' - Đại lý',
                'customer_type' => $customerType,
                'min_quantity' => 101,
                'max_quantity' => null,
                'unit_price' => $basePrice,
                'discount_percentage' => $this->getDiscountPercentage($customerType, 'large'),
                'discount_amount' => 0,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
                'season' => MaterialPricing::SEASON_ALL_YEAR,
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => true,
                'free_delivery_threshold' => null,
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => $isActive,
                'is_default' => false,
                'priority' => 3,
                'notes' => "Giá {$customerType} cho số lượng lớn - Kỳ " . ($periodIndex + 1),
                'conditions' => $this->getPricingConditions($customerType),
                'created_by' => $user->id,
            ]);
        }
    }

    /**
     * Create seasonal pricing for materials affected by weather.
     */
    private function createSeasonalPricing(BuildingMaterial $material, string $customerType, float $basePrice, User $user): void
    {
        $seasonalPricing = [
            [
                'season' => MaterialPricing::SEASON_DRY,
                'multiplier' => 0.95, // 5% discount in dry season
                'effective_from' => now()->addMonths(1),
                'effective_to' => now()->addMonths(3),
                'notes' => 'Giá mùa khô - giảm 5%'
            ],
            [
                'season' => MaterialPricing::SEASON_RAINY,
                'multiplier' => 1.1, // 10% increase in rainy season
                'effective_from' => now()->addMonths(4),
                'effective_to' => now()->addMonths(6),
                'notes' => 'Giá mùa mưa - tăng 10%'
            ],
            [
                'season' => MaterialPricing::SEASON_PEAK,
                'multiplier' => 1.15, // 15% increase in peak season
                'effective_from' => now()->addMonths(7),
                'effective_to' => now()->addMonths(9),
                'notes' => 'Giá mùa cao điểm - tăng 15%'
            ]
        ];

        foreach ($seasonalPricing as $seasonal) {
            MaterialPricing::create([
                'store_id' => $material->store_id,
                'material_id' => $material->id,
                'price_list_name' => ucfirst($customerType) . ' - ' . ucfirst($seasonal['season']),
                'customer_type' => $customerType,
                'min_quantity' => 1,
                'max_quantity' => null,
                'unit_price' => round($basePrice * $seasonal['multiplier'], -2),
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'effective_from' => $seasonal['effective_from'],
                'effective_to' => $seasonal['effective_to'],
                'season' => $seasonal['season'],
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => false,
                'free_delivery_threshold' => $this->getFreeDeliveryThreshold($customerType),
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => false, // Will be activated when season comes
                'is_default' => false,
                'priority' => 10,
                'notes' => $seasonal['notes'] . " cho {$customerType}",
                'conditions' => array_merge($this->getPricingConditions($customerType), [
                    'seasonal_adjustment' => $seasonal['notes']
                ]),
                'created_by' => $user->id,
            ]);
        }
    }

    /**
     * Get delivery areas.
     */
    private function getDeliveryAreas(): array
    {
        return ['TP.HCM', 'Đồng Nai', 'Bình Dương', 'Long An', 'Tây Ninh'];
    }

    /**
     * Get free delivery threshold.
     */
    private function getFreeDeliveryThreshold(string $customerType): ?float
    {
        return match($customerType) {
            MaterialPricing::CUSTOMER_RETAIL => 5000000,
            MaterialPricing::CUSTOMER_WHOLESALE => 3000000,
            MaterialPricing::CUSTOMER_CONTRACTOR => 2000000,
            MaterialPricing::CUSTOMER_VIP => 1000000,
            MaterialPricing::CUSTOMER_STAFF => 500000,
            default => 5000000,
        };
    }

    /**
     * Get discount percentage.
     */
    private function getDiscountPercentage(string $customerType, string $tier): float
    {
        $discounts = [
            MaterialPricing::CUSTOMER_RETAIL => ['medium' => 2, 'large' => 5],
            MaterialPricing::CUSTOMER_WHOLESALE => ['medium' => 3, 'large' => 7],
            MaterialPricing::CUSTOMER_CONTRACTOR => ['medium' => 5, 'large' => 10],
            MaterialPricing::CUSTOMER_VIP => ['medium' => 7, 'large' => 12],
            MaterialPricing::CUSTOMER_STAFF => ['medium' => 10, 'large' => 15],
        ];

        return $discounts[$customerType][$tier] ?? 0;
    }

    /**
     * Get pricing conditions.
     */
    private function getPricingConditions(string $customerType): array
    {
        $baseConditions = [
            'payment_terms' => 'Thanh toán trong 30 ngày',
            'warranty' => 'Bảo hành theo quy định nhà sản xuất',
        ];

        return match($customerType) {
            MaterialPricing::CUSTOMER_WHOLESALE => array_merge($baseConditions, [
                'min_order_value' => 'Đơn hàng tối thiểu 10 triệu',
                'credit_terms' => 'Hạn mức tín dụng theo thỏa thuận',
            ]),
            MaterialPricing::CUSTOMER_CONTRACTOR => array_merge($baseConditions, [
                'project_discount' => 'Chiết khấu dự án theo thỏa thuận',
                'bulk_delivery' => 'Giao hàng theo tiến độ dự án',
            ]),
            MaterialPricing::CUSTOMER_VIP => array_merge($baseConditions, [
                'priority_service' => 'Ưu tiên phục vụ',
                'extended_warranty' => 'Bảo hành mở rộng',
            ]),
            MaterialPricing::CUSTOMER_STAFF => array_merge($baseConditions, [
                'employee_discount' => 'Giá ưu đãi nhân viên',
                'flexible_payment' => 'Thanh toán linh hoạt',
            ]),
            default => $baseConditions,
        };
    }

    /**
     * Should create medium tier pricing.
     */
    private function shouldCreateMediumTier(BuildingMaterial $material): bool
    {
        // Don't create medium tier for very expensive items like steel
        return !str_contains(strtolower($material->name), 'thép');
    }

    /**
     * Should create large tier pricing.
     */
    private function shouldCreateLargeTier(BuildingMaterial $material): bool
    {
        // Create large tier for most materials except very expensive ones
        return !str_contains(strtolower($material->name), 'thép');
    }

    /**
     * Should create seasonal pricing.
     */
    private function shouldCreateSeasonalPricing(BuildingMaterial $material): bool
    {
        // Create seasonal pricing for cement and sand (affected by weather)
        $materialName = strtolower($material->name);
        return str_contains($materialName, 'xi măng') || str_contains($materialName, 'cát');
    }
}
