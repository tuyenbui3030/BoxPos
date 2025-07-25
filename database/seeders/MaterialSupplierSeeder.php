<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialSuppliers\Models\SupplierContact;
use Packages\User\Models\User;

class MaterialSupplierSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏭 Seeding Material Suppliers...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createSuppliersForStore($store);
        }

        $this->command->info('✅ Material Suppliers seeded successfully!');
    }

    private function createSuppliersForStore(Store $store): void
    {
        $suppliers = [
            [
                'supplier_code' => 'SUP001',
                'company_name' => 'Công ty TNHH Vật liệu Xây dựng Hòa Phát',
                'supplier_type' => 'manufacturer',
                'tax_code' => '0123456789',
                'phone' => '024-3876-5432',
                'email' => 'sales@hoaphat.com.vn',
                'website' => 'https://hoaphat.com.vn',
                'address' => 'Số 8 Phạm Hùng, Nam Từ Liêm, Hà Nội',
                'city' => 'Hà Nội',
                'country' => 'Việt Nam',
                'payment_terms' => 'net_30',
                'credit_limit' => 500000000,
                'is_active' => true,
                'rating' => 5,
                'notes' => 'Nhà cung cấp thép hàng đầu Việt Nam',
                'contacts' => [
                    [
                        'contact_name' => 'Nguyễn Văn A',
                        'contact_title' => 'Sales Manager',
                        'phone' => '024-3876-5432',
                        'email' => 'nguyenvana@hoaphat.com.vn',
                        'is_primary' => true,
                    ],
                    [
                        'contact_name' => 'Trần Thị B',
                        'contact_title' => 'Account Executive',
                        'phone' => '024-3876-5433',
                        'email' => 'tranthib@hoaphat.com.vn',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'supplier_code' => 'SUP002',
                'company_name' => 'Công ty Cổ phần Xi măng Hà Tiên 1',
                'supplier_type' => 'manufacturer',
                'tax_code' => '0987654321',
                'phone' => '0297-3950-888',
                'email' => 'info@hatien1.com',
                'website' => 'https://hatien1.com',
                'address' => 'Km 8, Quốc lộ 80, Hà Tiên, Kiên Giang',
                'city' => 'Kiên Giang',
                'country' => 'Việt Nam',
                'payment_terms' => 'net_15',
                'credit_limit' => 300000000,
                'is_active' => true,
                'rating' => 4,
                'notes' => 'Nhà sản xuất xi măng uy tín',
                'contacts' => [
                    [
                        'contact_name' => 'Lê Văn C',
                        'contact_title' => 'Regional Sales Manager',
                        'phone' => '0297-3950-888',
                        'email' => 'levanc@hatien1.com',
                        'is_primary' => true,
                    ],
                ],
            ],
            [
                'supplier_code' => 'SUP003',
                'company_name' => 'Công ty TNHH Gạch Đồng Tâm',
                'supplier_type' => 'manufacturer',
                'tax_code' => '0147258369',
                'phone' => '0274-3781-888',
                'email' => 'sales@dongtam.vn',
                'website' => 'https://dongtam.vn',
                'address' => 'Khu Công nghiệp Đồng Tâm, Tân Uyên, Bình Dương',
                'city' => 'Bình Dương',
                'country' => 'Việt Nam',
                'payment_terms' => 'net_30',
                'credit_limit' => 200000000,
                'is_active' => true,
                'rating' => 4,
                'notes' => 'Chuyên sản xuất gạch ốp lát cao cấp',
                'contacts' => [
                    [
                        'contact_name' => 'Phạm Thị D',
                        'contact_title' => 'Sales Director',
                        'phone' => '0274-3781-888',
                        'email' => 'phamthid@dongtam.vn',
                        'is_primary' => true,
                    ],
                ],
            ],
            [
                'supplier_code' => 'SUP004',
                'company_name' => 'Công ty TNHH Sơn Jotun Việt Nam',
                'supplier_type' => 'distributor',
                'tax_code' => '0369258147',
                'phone' => '028-3744-6666',
                'email' => 'vietnam@jotun.com',
                'website' => 'https://jotun.com/vn',
                'address' => 'Lầu 15, Tòa nhà Vietcombank, 5 Công Trường Mê Linh, Q.1, TP.HCM',
                'city' => 'TP. Hồ Chí Minh',
                'country' => 'Việt Nam',
                'payment_terms' => 'net_45',
                'credit_limit' => 150000000,
                'is_active' => true,
                'rating' => 5,
                'notes' => 'Thương hiệu sơn cao cấp từ Na Uy',
                'contacts' => [
                    [
                        'contact_name' => 'Võ Văn E',
                        'contact_title' => 'Country Manager',
                        'phone' => '028-3744-6666',
                        'email' => 'vovane@jotun.com',
                        'is_primary' => true,
                    ],
                ],
            ],
            [
                'supplier_code' => 'SUP005',
                'company_name' => 'Công ty TNHH Thiết bị Điện Schneider',
                'supplier_type' => 'distributor',
                'tax_code' => '0258147369',
                'phone' => '024-3936-0606',
                'email' => 'vietnam@schneider-electric.com',
                'website' => 'https://schneider-electric.vn',
                'address' => 'Tầng 15, Lotte Center, 54 Liễu Giai, Ba Đình, Hà Nội',
                'city' => 'Hà Nội',
                'country' => 'Việt Nam',
                'payment_terms' => 'net_30',
                'credit_limit' => 400000000,
                'is_active' => true,
                'rating' => 5,
                'notes' => 'Thiết bị điện công nghiệp hàng đầu',
                'contacts' => [
                    [
                        'contact_name' => 'Hoàng Văn F',
                        'contact_title' => 'Sales Manager',
                        'phone' => '024-3936-0606',
                        'email' => 'hoangvanf@schneider-electric.com',
                        'is_primary' => true,
                    ],
                ],
            ],
        ];

        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        foreach ($suppliers as $supplierData) {
            $contacts = $supplierData['contacts'];
            unset($supplierData['contacts']);

            $supplier = MaterialSupplier::create([
                ...$supplierData,
                'store_id' => $store->id,
                'created_by' => $createdBy?->id,
            ]);

            // Create contacts
            foreach ($contacts as $contactData) {
                SupplierContact::create([
                    ...$contactData,
                    'supplier_id' => $supplier->id,
                ]);
            }
        }
    }
}
