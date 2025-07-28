<?php

namespace Packages\MaterialSuppliers\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialSuppliers\Models\SupplierContact;
use Packages\Store\Models\Store;

class MaterialSupplierSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->logSeedingProgress('material_supplier_seeding_started');

            // Seed material suppliers for all stores
            $this->seedMaterialSuppliers();
            
            $this->logSeedingProgress('material_supplier_seeding_completed');
        });
    }

    /**
     * Seed material suppliers for all stores
     */
    private function seedMaterialSuppliers(): void
    {
        $this->logSeedingProgress('seeding_material_suppliers');
        
        $this->seedForAllStores(function (Store $store) {
            $this->createSuppliersForStore($store);
        });
    }

    /**
     * Create material suppliers for a specific store
     */
    private function createSuppliersForStore(Store $store): void
    {
        // First create standard suppliers manually
        $this->createStandardSuppliers($store->id);
        
        // Then create additional suppliers using factory
        $this->createFactorySuppliers($store);
    }

    /**
     * Create standard suppliers with realistic Vietnamese data
     */
    private function createStandardSuppliers(int $storeId): void
    {
        // Get a user from this store for created_by
        $store = \Packages\Store\Models\Store::find($storeId);
        $storeUser = $store ? $store->users()->first() : null;
        $adminUserId = $storeUser ? $storeUser->id : null;
        $suppliers = [
            [
                'company_name' => 'Công ty Xi măng Hà Tiên',
                'contact_person' => 'Nguyễn Văn An',
                'phone' => '0901234567',
                'email' => 'sales@hatiencement.com',
                'website' => 'https://hatiencement.com',
                'address' => '123 Đường Nguyễn Huệ',
                'city' => 'TP. Hồ Chí Minh',
                'province' => 'TP. Hồ Chí Minh',
                'postal_code' => '700000',
                'tax_code' => '0123456789',
                'supplier_type' => MaterialSupplier::TYPE_MANUFACTURER,
                'payment_terms' => MaterialSupplier::PAYMENT_NET_30,
                'credit_limit' => 500000000,
                'lead_time_days' => 5,
                'rating' => 4.5,
                'is_preferred' => true,
                'certifications' => ['ISO 9001', 'TCVN 2682'],
                'delivery_areas' => ['TP.HCM', 'Đồng Nai', 'Bình Dương'],
                'notes' => 'Nhà cung cấp xi măng uy tín, chất lượng cao',
            ],
            [
                'company_name' => 'Tập đoàn Hòa Phát',
                'contact_person' => 'Trần Thị Bình',
                'phone' => '0912345678',
                'email' => 'contact@hoaphat.com.vn',
                'website' => 'https://hoaphat.com.vn',
                'address' => '456 Đường Lê Lợi',
                'city' => 'Hà Nội',
                'province' => 'Hà Nội',
                'postal_code' => '100000',
                'tax_code' => '0987654321',
                'supplier_type' => MaterialSupplier::TYPE_MANUFACTURER,
                'payment_terms' => MaterialSupplier::PAYMENT_NET_30,
                'credit_limit' => 1000000000,
                'lead_time_days' => 7,
                'rating' => 4.8,
                'is_preferred' => true,
                'certifications' => ['ISO 9001', 'JIS G3112'],
                'delivery_areas' => ['Hà Nội', 'Hải Phòng', 'Quảng Ninh'],
                'notes' => 'Nhà sản xuất thép hàng đầu Việt Nam',
            ],
            [
                'company_name' => 'Công ty Gạch Đồng Tâm',
                'contact_person' => 'Lê Văn Cường',
                'phone' => '0923456789',
                'email' => 'info@dongtambrick.com',
                'website' => 'https://dongtambrick.com',
                'address' => '789 Đường Trần Hưng Đạo',
                'city' => 'Đồng Nai',
                'province' => 'Đồng Nai',
                'postal_code' => '810000',
                'tax_code' => '0111222333',
                'supplier_type' => MaterialSupplier::TYPE_MANUFACTURER,
                'payment_terms' => MaterialSupplier::PAYMENT_NET_15,
                'credit_limit' => 200000000,
                'lead_time_days' => 3,
                'rating' => 4.2,
                'is_preferred' => false,
                'certifications' => ['TCVN 1451'],
                'delivery_areas' => ['Đồng Nai', 'TP.HCM', 'Bình Dương'],
                'notes' => 'Chuyên sản xuất gạch xây dựng chất lượng cao',
            ],
            [
                'company_name' => 'Công ty Cát Đá Miền Nam',
                'contact_person' => 'Phạm Thị Dung',
                'phone' => '0934567890',
                'email' => 'sales@catdamiennam.com',
                'address' => '321 Đường Võ Văn Kiệt',
                'city' => 'An Giang',
                'province' => 'An Giang',
                'postal_code' => '880000',
                'tax_code' => '0444555666',
                'supplier_type' => MaterialSupplier::TYPE_DISTRIBUTOR,
                'payment_terms' => MaterialSupplier::PAYMENT_COD,
                'credit_limit' => 100000000,
                'lead_time_days' => 2,
                'rating' => 4.0,
                'is_preferred' => false,
                'certifications' => ['TCVN 7570'],
                'delivery_areas' => ['An Giang', 'Cần Thơ', 'Kiên Giang'],
                'notes' => 'Cung cấp cát đá chất lượng cho khu vực miền Tây',
            ],
            [
                'company_name' => 'Công ty Sơn Jotun Việt Nam',
                'contact_person' => 'Hoàng Văn Em',
                'phone' => '0945678901',
                'email' => 'vietnam@jotun.com',
                'website' => 'https://jotun.com/vn',
                'address' => '654 Đường Nguyễn Văn Cừ',
                'city' => 'TP. Hồ Chí Minh',
                'province' => 'TP. Hồ Chí Minh',
                'postal_code' => '700000',
                'tax_code' => '0777888999',
                'supplier_type' => MaterialSupplier::TYPE_DISTRIBUTOR,
                'payment_terms' => MaterialSupplier::PAYMENT_NET_30,
                'credit_limit' => 300000000,
                'lead_time_days' => 4,
                'rating' => 4.6,
                'is_preferred' => true,
                'certifications' => ['ISO 14001', 'Green Label'],
                'delivery_areas' => ['TP.HCM', 'Đồng Nai', 'Bình Dương', 'Long An'],
                'notes' => 'Thương hiệu sơn nổi tiếng thế giới',
            ],
        ];

        foreach ($suppliers as $index => $supplierData) {
            // Generate unique supplier code for this store
            $supplierCode = 'SUP' . str_pad($storeId, 3, '0', STR_PAD_LEFT) . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            $supplier = MaterialSupplier::create(array_merge($supplierData, [
                'store_id' => $storeId,
                'supplier_code' => $supplierCode,
                'country' => 'Vietnam',
                'current_balance' => 0,
                'is_active' => true,
                'created_by' => $adminUserId,
            ]));

            // Create contacts for each standard supplier
            $this->createContactsForSupplier($supplier);
        }

        $this->logSeedingProgress('created_standard_suppliers_for_store', [
            'store_id' => $storeId,
            'suppliers_count' => count($suppliers)
        ]);
    }

    /**
     * Create additional suppliers using factory
     */
    private function createFactorySuppliers(Store $store): void
    {
        // Get a user from this store for created_by
        $storeUser = $store->users()->first();
        $adminUserId = $storeUser ? $storeUser->id : null;
        
        $suppliersCount = $this->getRecordCount(15, 5);
        
        $suppliers = MaterialSupplier::factory()
            ->count($suppliersCount)
            ->withStore($store)
            ->create([
                'created_by' => $adminUserId,
            ]);

        // Create contacts for factory-generated suppliers
        foreach ($suppliers as $supplier) {
            $this->createContactsForSupplier($supplier);
        }

        $this->logSeedingProgress('created_factory_suppliers_for_store', [
            'store_id' => $store->id,
            'suppliers_count' => $suppliersCount
        ]);
    }

    /**
     * Create contacts for a supplier
     */
    private function createContactsForSupplier(MaterialSupplier $supplier): void
    {
        $contactsCount = $this->getRecordCount(3, 2);
        
        // Create primary contact first
        SupplierContact::factory()
            ->primary()
            ->sales()
            ->forSupplier($supplier)
            ->create();

        // Create additional contacts if needed
        if ($contactsCount > 1) {
            SupplierContact::factory()
                ->count($contactsCount - 1)
                ->forSupplier($supplier)
                ->create();
        }
    }
}