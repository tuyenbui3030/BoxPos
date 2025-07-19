<?php

namespace Packages\MaterialSuppliers\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\Store\Models\Store;

class MaterialSuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::active()->get();

        foreach ($stores as $store) {
            $this->createSuppliersForStore($store->id);
        }
    }

    /**
     * Create material suppliers for a specific store.
     */
    private function createSuppliersForStore(int $storeId): void
    {
        $suppliers = [
            [
                'supplier_code' => 'SUP000001',
                'company_name' => 'Công ty Xi măng Hà Tiên',
                'contact_person' => 'Nguyễn Văn A',
                'phone' => '0901234567',
                'email' => 'sales@hatiencement.com',
                'website' => 'https://hatiencement.com',
                'address' => '123 Đường Nguyễn Huệ',
                'city' => 'TP. Hồ Chí Minh',
                'province' => 'TP. Hồ Chí Minh',
                'postal_code' => '700000',
                'tax_code' => '0123456789',
                'supplier_type' => 'manufacturer',
                'payment_terms' => 'net_30',
                'credit_limit' => 500000000,
                'lead_time_days' => 5,
                'rating' => 4.5,
                'is_preferred' => true,
                'certifications' => ['ISO 9001', 'TCVN 2682'],
                'delivery_areas' => ['TP.HCM', 'Đồng Nai', 'Bình Dương'],
                'notes' => 'Nhà cung cấp xi măng uy tín, chất lượng cao',
            ],
            [
                'supplier_code' => 'SUP000002',
                'company_name' => 'Tập đoàn Hòa Phát',
                'contact_person' => 'Trần Thị B',
                'phone' => '0912345678',
                'email' => 'contact@hoaphat.com.vn',
                'website' => 'https://hoaphat.com.vn',
                'address' => '456 Đường Lê Lợi',
                'city' => 'Hà Nội',
                'province' => 'Hà Nội',
                'postal_code' => '100000',
                'tax_code' => '0987654321',
                'supplier_type' => 'manufacturer',
                'payment_terms' => 'net_30',
                'credit_limit' => 1000000000,
                'lead_time_days' => 7,
                'rating' => 4.8,
                'is_preferred' => true,
                'certifications' => ['ISO 9001', 'JIS G3112'],
                'delivery_areas' => ['Hà Nội', 'Hải Phòng', 'Quảng Ninh'],
                'notes' => 'Nhà sản xuất thép hàng đầu Việt Nam',
            ],
            [
                'supplier_code' => 'SUP000003',
                'company_name' => 'Công ty Gạch Đồng Tâm',
                'contact_person' => 'Lê Văn C',
                'phone' => '0923456789',
                'email' => 'info@dongtambrick.com',
                'website' => 'https://dongtambrick.com',
                'address' => '789 Đường Trần Hưng Đạo',
                'city' => 'Đồng Nai',
                'province' => 'Đồng Nai',
                'postal_code' => '810000',
                'tax_code' => '0111222333',
                'supplier_type' => 'manufacturer',
                'payment_terms' => 'net_15',
                'credit_limit' => 200000000,
                'lead_time_days' => 3,
                'rating' => 4.2,
                'is_preferred' => false,
                'certifications' => ['TCVN 1451'],
                'delivery_areas' => ['Đồng Nai', 'TP.HCM', 'Bình Dương'],
                'notes' => 'Chuyên sản xuất gạch xây dựng chất lượng cao',
            ],
            [
                'supplier_code' => 'SUP000004',
                'company_name' => 'Công ty Cát Đá Miền Nam',
                'contact_person' => 'Phạm Thị D',
                'phone' => '0934567890',
                'email' => 'sales@catdamiennam.com',
                'address' => '321 Đường Võ Văn Kiệt',
                'city' => 'An Giang',
                'province' => 'An Giang',
                'postal_code' => '880000',
                'tax_code' => '0444555666',
                'supplier_type' => 'distributor',
                'payment_terms' => 'cod',
                'credit_limit' => 100000000,
                'lead_time_days' => 2,
                'rating' => 4.0,
                'is_preferred' => false,
                'certifications' => ['TCVN 7570'],
                'delivery_areas' => ['An Giang', 'Cần Thơ', 'Kiên Giang'],
                'notes' => 'Cung cấp cát đá chất lượng cho khu vực miền Tây',
            ],
            [
                'supplier_code' => 'SUP000005',
                'company_name' => 'Công ty Sơn Jotun Việt Nam',
                'contact_person' => 'Hoàng Văn E',
                'phone' => '0945678901',
                'email' => 'vietnam@jotun.com',
                'website' => 'https://jotun.com/vn',
                'address' => '654 Đường Nguyễn Văn Cừ',
                'city' => 'TP. Hồ Chí Minh',
                'province' => 'TP. Hồ Chí Minh',
                'postal_code' => '700000',
                'tax_code' => '0777888999',
                'supplier_type' => 'distributor',
                'payment_terms' => 'net_30',
                'credit_limit' => 300000000,
                'lead_time_days' => 4,
                'rating' => 4.6,
                'is_preferred' => true,
                'certifications' => ['ISO 14001', 'Green Label'],
                'delivery_areas' => ['TP.HCM', 'Đồng Nai', 'Bình Dương', 'Long An'],
                'notes' => 'Thương hiệu sơn nổi tiếng thế giới',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            MaterialSupplier::create(array_merge($supplierData, [
                'store_id' => $storeId,
                'country' => 'Vietnam',
                'current_balance' => 0,
                'is_active' => true,
            ]));
        }
    }
}
