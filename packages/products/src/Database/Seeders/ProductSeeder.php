<?php

namespace Packages\Products\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Products\Models\Product;
use Packages\Products\Models\ProductCategory;
use Packages\Products\Models\ProductVariant;
use Packages\Products\Models\Service;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class ProductSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedProductCategories();
            $this->seedProducts();
            $this->seedServices();
        });
    }

    /**
     * Seed product categories for all stores
     */
    private function seedProductCategories(): void
    {
        $this->logSeedingProgress('product_categories_seeding_started');

        $stores = $this->getStores();
        $categoriesCount = $this->getConfigValue('product_categories_count', 10);

        foreach ($stores as $store) {
            $this->seedCategoriesForStore($store, $categoriesCount);
        }

        $this->logSeedingProgress('product_categories_seeding_completed', [
            'total_stores' => $stores->count(),
            'categories_per_store' => $categoriesCount
        ]);
    }

    /**
     * Seed product categories for a specific store
     */
    private function seedCategoriesForStore(Store $store, int $count): void
    {
        $this->logSeedingProgress('seeding_categories_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'categories_count' => $count
        ]);

        // Create main service categories
        $serviceCategories = [
            'Dịch vụ thi công',
            'Dịch vụ vận chuyển', 
            'Dịch vụ tư vấn',
            'Dịch vụ bảo trì',
            'Dịch vụ thiết kế',
            'Dịch vụ lắp đặt',
            'Dịch vụ sửa chữa',
            'Dịch vụ khác'
        ];

        $createdCategories = [];
        
        // Create main categories
        foreach (array_slice($serviceCategories, 0, min($count, count($serviceCategories))) as $index => $categoryName) {
            $category = ProductCategory::create([
                'store_id' => $store->id,
                'parent_id' => null,
                'code' => 'CAT' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'name' => $categoryName,
                'description' => "Danh mục {$categoryName} chuyên nghiệp",
                'image' => null,
                'color' => $this->getRandomColor(),
                'is_active' => true,
                'sort_order' => ($index + 1) * 10,
                'metadata' => [],
            ]);
            
            $createdCategories[] = $category;
        }

        // Create some subcategories if we have remaining count
        $remainingCount = $count - count($createdCategories);
        if ($remainingCount > 0 && !empty($createdCategories)) {
            $this->createSubcategories($store, $createdCategories, $remainingCount);
        }
    }

    /**
     * Create subcategories for existing categories
     */
    private function createSubcategories(Store $store, array $parentCategories, int $count): void
    {
        $subcategoryTemplates = [
            'Dịch vụ thi công' => ['Thi công nhà ở', 'Thi công công nghiệp', 'Thi công cơ sở hạ tầng'],
            'Dịch vụ vận chuyển' => ['Vận chuyển vật liệu', 'Vận chuyển thiết bị', 'Vận chuyển đặc biệt'],
            'Dịch vụ tư vấn' => ['Tư vấn thiết kế', 'Tư vấn pháp lý', 'Tư vấn kỹ thuật'],
            'Dịch vụ bảo trì' => ['Bảo trì định kỳ', 'Bảo trì khẩn cấp', 'Bảo trì chuyên sâu'],
        ];

        $created = 0;
        foreach ($parentCategories as $parent) {
            if ($created >= $count) break;
            
            $subcategories = $subcategoryTemplates[$parent->name] ?? ['Dịch vụ cơ bản', 'Dịch vụ nâng cao'];
            
            foreach ($subcategories as $subName) {
                if ($created >= $count) break;
                
                ProductCategory::create([
                    'store_id' => $store->id,
                    'parent_id' => $parent->id,
                    'code' => $parent->code . '-' . str_pad($created + 1, 2, '0', STR_PAD_LEFT),
                    'name' => $subName,
                    'description' => "Danh mục con của {$parent->name}",
                    'image' => null,
                    'color' => $parent->color,
                    'is_active' => true,
                    'sort_order' => ($created + 1) * 5,
                    'metadata' => [],
                ]);
                
                $created++;
            }
        }
    }

    /**
     * Seed products for all stores
     */
    private function seedProducts(): void
    {
        $this->logSeedingProgress('products_seeding_started');

        $stores = $this->getStores();
        $productsPerCategory = $this->getConfigValue('products_per_category', 8);

        foreach ($stores as $store) {
            $this->seedProductsForStore($store, $productsPerCategory);
        }

        $this->logSeedingProgress('products_seeding_completed');
    }

    /**
     * Seed products for a specific store
     */
    private function seedProductsForStore(Store $store, int $productsPerCategory): void
    {
        $this->logSeedingProgress('seeding_products_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'products_per_category' => $productsPerCategory
        ]);

        $categories = ProductCategory::where('store_id', $store->id)->get();
        $users = User::all();

        foreach ($categories as $category) {
            // Create simple products
            $simpleCount = intval($productsPerCategory * 0.7); // 70% simple products
            for ($i = 0; $i < $simpleCount; $i++) {
                $this->createSimpleProduct($store, $category, $users);
            }

            // Create variable products with variants
            $variableCount = $productsPerCategory - $simpleCount; // 30% variable products
            for ($i = 0; $i < $variableCount; $i++) {
                $product = $this->createVariableProduct($store, $category, $users);
                $this->createProductVariants($product);
            }
        }
    }

    /**
     * Create a simple product
     */
    private function createSimpleProduct(Store $store, ProductCategory $category, $users): Product
    {
        $costPrice = fake()->randomFloat(2, 10, 500);
        $sellingPrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);
        $stockQuantity = fake()->numberBetween(10, 100);
        
        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'sku' => Product::generateSKU($store->id),
            'barcode' => fake()->unique()->ean13(),
            'name' => $this->getVietnameseProductName($category->name),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'type' => 'simple',
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
            'is_digital' => false,
            'is_service' => false,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'sale_price' => fake()->boolean(30) ? $sellingPrice * 0.9 : null,
            'currency' => 'VND',
            'is_taxable' => fake()->boolean(80),
            'tax_rate' => fake()->randomElement([0, 5, 10]),
            'tax_class' => 'standard',
            'track_inventory' => true,
            'stock_quantity' => $stockQuantity,
            'reserved_quantity' => 0,
            'available_quantity' => $stockQuantity,
            'unit' => fake()->randomElement(['cái', 'bộ', 'chiếc', 'gói', 'hộp', 'lần']),
            'min_stock_level' => fake()->numberBetween(5, 20),
            'max_stock_level' => fake()->numberBetween(100, 500),
            'reorder_point' => fake()->numberBetween(10, 30),
            'reorder_quantity' => fake()->numberBetween(50, 200),
            'weight' => fake()->randomFloat(2, 0.1, 10),
            'weight_unit' => 'kg',
            'allow_backorder' => fake()->boolean(20),
            'is_featured' => fake()->boolean(10),
            'requires_shipping' => fake()->boolean(80),
            'sort_order' => fake()->numberBetween(1, 1000),
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
        ]);
    }

    /**
     * Create a variable product
     */
    private function createVariableProduct(Store $store, ProductCategory $category, $users): Product
    {
        $costPrice = fake()->randomFloat(2, 20, 800);
        $sellingPrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);
        
        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'sku' => Product::generateSKU($store->id),
            'barcode' => fake()->unique()->ean13(),
            'name' => $this->getVietnameseProductName($category->name),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'type' => 'variable',
            'status' => 'active',
            'is_digital' => false,
            'is_service' => false,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'currency' => 'VND',
            'is_taxable' => fake()->boolean(80),
            'tax_rate' => fake()->randomElement([0, 5, 10]),
            'tax_class' => 'standard',
            'track_inventory' => true,
            'stock_quantity' => 0, // Variable products track stock via variants
            'reserved_quantity' => 0,
            'available_quantity' => 0,
            'unit' => fake()->randomElement(['cái', 'bộ', 'chiếc', 'gói', 'hộp']),
            'allow_backorder' => fake()->boolean(20),
            'is_featured' => fake()->boolean(15),
            'requires_shipping' => fake()->boolean(80),
            'sort_order' => fake()->numberBetween(1, 1000),
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
        ]);
    }

    /**
     * Create variants for a variable product
     */
    private function createProductVariants(Product $product): void
    {
        $variantCount = fake()->numberBetween(2, 4);
        $colors = ['Đỏ', 'Xanh', 'Vàng', 'Trắng', 'Đen'];
        $sizes = ['S', 'M', 'L', 'XL'];
        
        for ($i = 0; $i < $variantCount; $i++) {
            $costPrice = $product->cost_price * fake()->randomFloat(2, 0.9, 1.1);
            $sellingPrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);
            $stockQuantity = fake()->numberBetween(5, 50);
            
            $attributes = [];
            $attributeSummary = [];
            
            if (fake()->boolean(70)) {
                $color = fake()->randomElement($colors);
                $attributes['color'] = $color;
                $attributeSummary[] = $color;
            }
            
            if (fake()->boolean(50)) {
                $size = fake()->randomElement($sizes);
                $attributes['size'] = $size;
                $attributeSummary[] = $size;
            }
            
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $product->sku . '-V' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'barcode' => fake()->unique()->ean13(),
                'name' => $product->name . ' - ' . implode(', ', $attributeSummary),
                'attributes' => $attributes,
                'attribute_summary' => implode(', ', $attributeSummary),
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'stock_quantity' => $stockQuantity,
                'reserved_quantity' => 0,
                'available_quantity' => $stockQuantity,
                'min_stock_level' => fake()->numberBetween(2, 10),
                'reorder_point' => fake()->numberBetween(5, 15),
                'status' => 'active',
                'is_default' => $i === 0, // First variant is default
                'sort_order' => $i + 1,
            ]);
        }
    }

    /**
     * Seed services for all stores
     */
    private function seedServices(): void
    {
        $this->logSeedingProgress('services_seeding_started');

        $stores = $this->getStores();
        $servicesPerCategory = $this->getConfigValue('products_per_category', 8) / 2; // Half as many services as products

        foreach ($stores as $store) {
            $this->seedServicesForStore($store, intval($servicesPerCategory));
        }

        $this->logSeedingProgress('services_seeding_completed');
    }

    /**
     * Seed services for a specific store
     */
    private function seedServicesForStore(Store $store, int $servicesPerCategory): void
    {
        $this->logSeedingProgress('seeding_services_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'services_per_category' => $servicesPerCategory
        ]);

        $categories = ProductCategory::where('store_id', $store->id)->get();
        $users = User::all();

        foreach ($categories as $category) {
            for ($i = 0; $i < $servicesPerCategory; $i++) {
                $this->createService($store, $category, $users);
            }
        }
    }

    /**
     * Create a service
     */
    private function createService(Store $store, ProductCategory $category, $users): Service
    {
        $basePrice = fake()->randomFloat(2, 100, 5000);
        $hourlyRate = fake()->randomFloat(2, 50, 1000);
        
        return Service::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'code' => Service::generateCode($store->id),
            'name' => $this->getVietnameseServiceName($category->name),
            'description' => fake()->paragraph(),
            'short_description' => fake()->sentence(),
            'type' => fake()->randomElement(['standard', 'custom', 'one_time']),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
            'billing_type' => fake()->randomElement(['fixed', 'hourly', 'daily']),
            'base_price' => $basePrice,
            'hourly_rate' => $hourlyRate,
            'setup_fee' => fake()->boolean(30) ? fake()->randomFloat(2, 50, 500) : 0,
            'currency' => 'VND',
            'estimated_duration' => fake()->numberBetween(60, 480), // 1-8 hours
            'min_duration' => fake()->numberBetween(30, 120),
            'max_duration' => fake()->numberBetween(240, 960),
            'requires_booking' => fake()->boolean(70),
            'booking_lead_time' => fake()->numberBetween(2, 48),
            'available_days' => fake()->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'], fake()->numberBetween(4, 6)),
            'is_taxable' => fake()->boolean(80),
            'tax_rate' => fake()->randomElement([0, 5, 10]),
            'tax_class' => 'standard',
            'delivery_method' => fake()->randomElement(['in_person', 'on_site', 'hybrid']),
            'requires_materials' => fake()->boolean(60),
            'requires_staff' => fake()->boolean(80),
            'min_staff' => fake()->numberBetween(1, 2),
            'max_staff' => fake()->numberBetween(2, 5),
            'is_featured' => fake()->boolean(10),
            'allow_online_booking' => fake()->boolean(60),
            'send_confirmation' => fake()->boolean(80),
            'send_reminder' => fake()->boolean(70),
            'reminder_hours' => fake()->numberBetween(2, 24),
            'sort_order' => fake()->numberBetween(1, 100),
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
        ]);
    }

    /**
     * Get Vietnamese product names based on category
     */
    private function getVietnameseProductName(string $categoryName): string
    {
        $productTemplates = [
            'Dịch vụ thi công' => [
                'Gói thi công nhà cấp 4',
                'Gói thi công biệt thự',
                'Gói thi công nhà phố',
                'Gói thi công chung cư',
                'Gói thi công văn phòng'
            ],
            'Dịch vụ vận chuyển' => [
                'Gói vận chuyển xi măng',
                'Gói vận chuyển sắt thép',
                'Gói vận chuyển gạch',
                'Gói vận chuyển cát đá',
                'Gói vận chuyển thiết bị'
            ],
            'Dịch vụ tư vấn' => [
                'Gói tư vấn thiết kế',
                'Gói tư vấn pháp lý',
                'Gói tư vấn kỹ thuật',
                'Gói tư vấn giám sát',
                'Gói tư vấn quản lý'
            ],
            'default' => [
                'Gói dịch vụ cơ bản',
                'Gói dịch vụ nâng cao',
                'Gói dịch vụ chuyên nghiệp',
                'Gói dịch vụ cao cấp',
                'Gói dịch vụ đặc biệt'
            ]
        ];

        $templates = $productTemplates[$categoryName] ?? $productTemplates['default'];
        return fake()->randomElement($templates);
    }

    /**
     * Get Vietnamese service names based on category
     */
    private function getVietnameseServiceName(string $categoryName): string
    {
        $serviceTemplates = [
            'Dịch vụ thi công' => [
                'Thi công móng nhà',
                'Thi công tường',
                'Thi công mái',
                'Thi công sàn',
                'Thi công hoàn thiện'
            ],
            'Dịch vụ vận chuyển' => [
                'Vận chuyển trong thành phố',
                'Vận chuyển liên tỉnh',
                'Vận chuyển hàng nặng',
                'Vận chuyển khẩn cấp',
                'Vận chuyển đặc biệt'
            ],
            'Dịch vụ tư vấn' => [
                'Tư vấn thiết kế kiến trúc',
                'Tư vấn thủ tục pháp lý',
                'Tư vấn kỹ thuật xây dựng',
                'Tư vấn quản lý dự án',
                'Tư vấn nghiệm thu'
            ],
            'default' => [
                'Dịch vụ chuyên nghiệp',
                'Dịch vụ tư vấn',
                'Dịch vụ hỗ trợ',
                'Dịch vụ bảo trì',
                'Dịch vụ khác'
            ]
        ];

        $templates = $serviceTemplates[$categoryName] ?? $serviceTemplates['default'];
        return fake()->randomElement($templates);
    }

    /**
     * Get random color for categories
     */
    private function getRandomColor(): string
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
        ];
        
        return fake()->randomElement($colors);
    }
}