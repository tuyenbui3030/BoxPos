<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialPricing\Models\MaterialPricing;
use Packages\User\Models\User;
use Carbon\Carbon;

class MaterialPricingSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💰 Seeding Material Pricing...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createPricingForStore($store);
        }

        $this->command->info('✅ Material Pricing seeded successfully!');
    }

    private function createPricingForStore(Store $store): void
    {
        $materials = BuildingMaterial::where('store_id', $store->id)->get();
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        foreach ($materials as $material) {
            $this->createPricingForMaterial($store, $material, $createdBy);
        }
    }

    private function createPricingForMaterial(Store $store, BuildingMaterial $material, ?User $createdBy): void
    {
        $baseCost = rand(50000, 1000000); // Base cost in VND
        
        // Create pricing for different customer types
        $customerTypes = [
            'retail' => [
                'markup' => 1.5, // 50% markup
                'price_list_name' => 'Bảng giá bán lẻ',
                'is_default' => true,
                'priority' => 1,
            ],
            'wholesale' => [
                'markup' => 1.3, // 30% markup
                'price_list_name' => 'Bảng giá bán sỉ',
                'is_default' => false,
                'priority' => 2,
            ],
            'contractor' => [
                'markup' => 1.25, // 25% markup
                'price_list_name' => 'Bảng giá thầu xây dựng',
                'is_default' => false,
                'priority' => 3,
            ],
            'vip' => [
                'markup' => 1.2, // 20% markup
                'price_list_name' => 'Bảng giá khách hàng VIP',
                'is_default' => false,
                'priority' => 4,
            ],
            'staff' => [
                'markup' => 1.1, // 10% markup
                'price_list_name' => 'Bảng giá nhân viên',
                'is_default' => false,
                'priority' => 5,
            ],
        ];

        foreach ($customerTypes as $customerType => $config) {
            // Create quantity-based pricing tiers
            $this->createQuantityTiers($store, $material, $customerType, $config, $baseCost, $createdBy);
        }

        // Create seasonal pricing for some materials
        if (rand(0, 1) == 1) {
            $this->createSeasonalPricing($store, $material, $baseCost, $createdBy);
        }
    }

    private function createQuantityTiers(Store $store, BuildingMaterial $material, string $customerType, array $config, int $baseCost, ?User $createdBy): void
    {
        $basePrice = $baseCost * $config['markup'];
        
        $tiers = [
            [
                'min_quantity' => 1,
                'max_quantity' => 10,
                'discount_percentage' => 0,
                'notes' => 'Giá lẻ cơ bản',
            ],
            [
                'min_quantity' => 11,
                'max_quantity' => 50,
                'discount_percentage' => 5,
                'notes' => 'Giảm giá 5% cho đơn hàng từ 11-50 đơn vị',
            ],
            [
                'min_quantity' => 51,
                'max_quantity' => 100,
                'discount_percentage' => 10,
                'notes' => 'Giảm giá 10% cho đơn hàng từ 51-100 đơn vị',
            ],
            [
                'min_quantity' => 101,
                'max_quantity' => null,
                'discount_percentage' => 15,
                'notes' => 'Giảm giá 15% cho đơn hàng trên 100 đơn vị',
            ],
        ];

        foreach ($tiers as $index => $tier) {
            $discountAmount = $basePrice * ($tier['discount_percentage'] / 100);
            $finalPrice = $basePrice - $discountAmount;

            MaterialPricing::create([
                'store_id' => $store->id,
                'material_id' => $material->id,
                'price_list_name' => $config['price_list_name'],
                'customer_type' => $customerType,
                'min_quantity' => $tier['min_quantity'],
                'max_quantity' => $tier['max_quantity'],
                'unit_price' => $finalPrice,
                'discount_percentage' => $tier['discount_percentage'],
                'discount_amount' => $discountAmount,
                'effective_from' => Carbon::now()->subDays(30),
                'effective_to' => Carbon::now()->addMonths(6),
                'season' => 'all_year',
                'delivery_areas' => json_encode(['Hà Nội', 'TP.HCM', 'Đà Nẵng']),
                'delivery_surcharge' => $this->calculateDeliverySurcharge($finalPrice),
                'free_delivery' => $finalPrice > 1000000,
                'free_delivery_threshold' => 1000000,
                'currency' => 'VND',
                'tax_rate' => 10,
                'tax_inclusive' => false,
                'is_active' => true,
                'is_default' => $config['is_default'] && $index === 0,
                'priority' => $config['priority'],
                'notes' => $tier['notes'],
                'conditions' => json_encode([
                    'payment_terms' => $customerType === 'retail' ? 'cash' : 'net_30',
                    'minimum_order' => $tier['min_quantity'],
                    'bulk_discount' => $tier['discount_percentage'] > 0,
                ]),
                'created_by' => $createdBy?->id,
            ]);
        }
    }

    private function createSeasonalPricing(Store $store, BuildingMaterial $material, int $baseCost, ?User $createdBy): void
    {
        $seasons = [
            'dry_season' => [
                'markup' => 1.4, // Higher price in dry season (construction peak)
                'effective_from' => Carbon::create(date('Y'), 11, 1), // Nov 1
                'effective_to' => Carbon::create(date('Y') + 1, 4, 30), // Apr 30
                'notes' => 'Giá mùa khô - cao điểm xây dựng',
            ],
            'rainy_season' => [
                'markup' => 1.2, // Lower price in rainy season
                'effective_from' => Carbon::create(date('Y'), 5, 1), // May 1
                'effective_to' => Carbon::create(date('Y'), 10, 31), // Oct 31
                'notes' => 'Giá mùa mưa - ưu đãi đặc biệt',
            ],
        ];

        foreach ($seasons as $season => $config) {
            $seasonalPrice = $baseCost * $config['markup'];

            MaterialPricing::create([
                'store_id' => $store->id,
                'material_id' => $material->id,
                'price_list_name' => 'Bảng giá theo mùa',
                'customer_type' => 'retail',
                'min_quantity' => 1,
                'max_quantity' => null,
                'unit_price' => $seasonalPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'effective_from' => $config['effective_from'],
                'effective_to' => $config['effective_to'],
                'season' => $season,
                'delivery_areas' => json_encode(['Toàn quốc']),
                'delivery_surcharge' => $this->calculateDeliverySurcharge($seasonalPrice),
                'free_delivery' => false,
                'free_delivery_threshold' => 2000000,
                'currency' => 'VND',
                'tax_rate' => 10,
                'tax_inclusive' => false,
                'is_active' => true,
                'is_default' => false,
                'priority' => 10, // Lower priority than regular pricing
                'notes' => $config['notes'],
                'conditions' => json_encode([
                    'seasonal_pricing' => true,
                    'weather_dependent' => true,
                ]),
                'created_by' => $createdBy?->id,
            ]);
        }
    }

    private function calculateDeliverySurcharge(float $price): float
    {
        if ($price < 500000) {
            return 50000; // 50k for small orders
        } elseif ($price < 1000000) {
            return 30000; // 30k for medium orders
        } else {
            return 0; // Free delivery for large orders
        }
    }
}
