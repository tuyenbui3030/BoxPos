<?php

namespace Packages\MaterialPricing\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialPricing\Models\MaterialPricing;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class MaterialPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createPricingForStore($store->id);
        }
    }

    /**
     * Create pricing records for a specific store.
     */
    private function createPricingForStore(int $storeId): void
    {
        $materials = BuildingMaterial::where('store_id', $storeId)->get();
        $users = User::whereHas('stores', function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })->get();

        $customerTypes = ['retail', 'wholesale', 'contractor', 'vip', 'staff'];
        $seasons = ['all_year', 'dry_season', 'rainy_season', 'peak_season'];

        foreach ($materials as $material) {
            $this->createPricingForMaterial($material, $customerTypes, $seasons, $users);
        }
    }

    /**
     * Create pricing for a specific material.
     */
    private function createPricingForMaterial(BuildingMaterial $material, array $customerTypes, array $seasons, $users): void
    {
        $basePrices = $this->getBasePricesForMaterial($material);

        foreach ($customerTypes as $customerType) {
            // Create quantity-based pricing tiers
            $this->createQuantityTiers($material, $customerType, $basePrices, $seasons, $users);
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
                'retail' => 115000,
                'wholesale' => 105000,
                'contractor' => 100000,
                'vip' => 95000,
                'staff' => 90000,
            ];
        } elseif (str_contains($materialName, 'thép')) {
            return [
                'retail' => 18500000,
                'wholesale' => 17500000,
                'contractor' => 17000000,
                'vip' => 16500000,
                'staff' => 16000000,
            ];
        } elseif (str_contains($materialName, 'gạch')) {
            return [
                'retail' => 600,
                'wholesale' => 500,
                'contractor' => 450,
                'vip' => 420,
                'staff' => 400,
            ];
        } elseif (str_contains($materialName, 'cát')) {
            return [
                'retail' => 450000,
                'wholesale' => 400000,
                'contractor' => 380000,
                'vip' => 360000,
                'staff' => 350000,
            ];
        } elseif (str_contains($materialName, 'sơn')) {
            return [
                'retail' => 280000,
                'wholesale' => 250000,
                'contractor' => 230000,
                'vip' => 210000,
                'staff' => 200000,
            ];
        } else {
            return [
                'retail' => 100000,
                'wholesale' => 90000,
                'contractor' => 85000,
                'vip' => 80000,
                'staff' => 75000,
            ];
        }
    }

    /**
     * Create quantity-based pricing tiers.
     */
    private function createQuantityTiers(BuildingMaterial $material, string $customerType, array $basePrices, array $seasons, $users): void
    {
        $basePrice = $basePrices[$customerType];
        $user = $users->random();

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
            'effective_from' => now()->subDays(30),
            'effective_to' => now()->addMonths(6),
            'season' => 'all_year',
            'delivery_areas' => $this->getDeliveryAreas(),
            'delivery_surcharge' => $customerType === 'retail' ? 50000 : 0,
            'free_delivery' => false,
            'free_delivery_threshold' => $this->getFreeDeliveryThreshold($customerType),
            'currency' => 'VND',
            'tax_rate' => 10.0,
            'tax_inclusive' => false,
            'is_active' => true,
            'is_default' => $customerType === 'retail',
            'priority' => 1,
            'notes' => "Giá {$customerType} cho số lượng nhỏ",
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
                'effective_from' => now()->subDays(30),
                'effective_to' => now()->addMonths(6),
                'season' => 'all_year',
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => true,
                'free_delivery_threshold' => null,
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
                'priority' => 2,
                'notes' => "Giá {$customerType} cho số lượng trung bình",
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
                'effective_from' => now()->subDays(30),
                'effective_to' => now()->addMonths(6),
                'season' => 'all_year',
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => true,
                'free_delivery_threshold' => null,
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
                'priority' => 3,
                'notes' => "Giá {$customerType} cho số lượng lớn",
                'conditions' => $this->getPricingConditions($customerType),
                'created_by' => $user->id,
            ]);
        }

        // Seasonal pricing for some materials
        if ($this->shouldCreateSeasonalPricing($material)) {
            MaterialPricing::create([
                'store_id' => $material->store_id,
                'material_id' => $material->id,
                'price_list_name' => ucfirst($customerType) . ' - Mùa cao điểm',
                'customer_type' => $customerType,
                'min_quantity' => 1,
                'max_quantity' => null,
                'unit_price' => $basePrice * 1.1, // 10% increase
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'effective_from' => now()->addMonths(2),
                'effective_to' => now()->addMonths(4),
                'season' => 'peak_season',
                'delivery_areas' => $this->getDeliveryAreas(),
                'delivery_surcharge' => 0,
                'free_delivery' => false,
                'free_delivery_threshold' => $this->getFreeDeliveryThreshold($customerType),
                'currency' => 'VND',
                'tax_rate' => 10.0,
                'tax_inclusive' => false,
                'is_active' => false, // Will be activated later
                'is_default' => false,
                'priority' => 10,
                'notes' => "Giá mùa cao điểm cho {$customerType}",
                'conditions' => array_merge($this->getPricingConditions($customerType), [
                    'seasonal_surcharge' => '10% tăng giá mùa cao điểm'
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
            'retail' => 5000000,
            'wholesale' => 3000000,
            'contractor' => 2000000,
            'vip' => 1000000,
            'staff' => 500000,
            default => 5000000,
        };
    }

    /**
     * Get discount percentage.
     */
    private function getDiscountPercentage(string $customerType, string $tier): float
    {
        $discounts = [
            'retail' => ['medium' => 2, 'large' => 5],
            'wholesale' => ['medium' => 3, 'large' => 7],
            'contractor' => ['medium' => 5, 'large' => 10],
            'vip' => ['medium' => 7, 'large' => 12],
            'staff' => ['medium' => 10, 'large' => 15],
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
            'wholesale' => array_merge($baseConditions, [
                'min_order_value' => 'Đơn hàng tối thiểu 10 triệu',
                'credit_terms' => 'Hạn mức tín dụng theo thỏa thuận',
            ]),
            'contractor' => array_merge($baseConditions, [
                'project_discount' => 'Chiết khấu dự án theo thỏa thuận',
                'bulk_delivery' => 'Giao hàng theo tiến độ dự án',
            ]),
            'vip' => array_merge($baseConditions, [
                'priority_service' => 'Ưu tiên phục vụ',
                'extended_warranty' => 'Bảo hành mở rộng',
            ]),
            'staff' => array_merge($baseConditions, [
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
