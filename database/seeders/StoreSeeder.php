<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo dữ liệu mẫu cho stores theo nghiệp vụ multi-tenant
     */
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Quản lý Vật liệu Xây dựng ABC',
                'slug' => 'vat-lieu-xay-dung',
                'domain' => 'vatlieu.boxpos.local',
                'description' => 'Hệ thống quản lý vật liệu xây dựng - Công ty ABC',
                'address' => '123 Đường Xây Dựng, Quận Bình Thạnh, TP.HCM',
                'phone' => '0901234567',
                'email' => 'admin@vatlieu-abc.com',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
                'settings' => json_encode([
                    'currency' => 'VND',
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'tax_rate' => 10,
                    'business_type' => 'materials_management',
                    'industry' => 'construction',
                    'features' => [
                        'batch_tracking',
                        'expiry_management',
                        'supplier_management',
                        'project_allocation',
                        'quality_control'
                    ]
                ]),
                'status' => 'active'
            ],
            [
                'name' => 'Kho Cà phê Highlands Coffee',
                'slug' => 'kho-ca-phe',
                'domain' => 'coffee.boxpos.local',
                'description' => 'Hệ thống quản lý kho cà phê - Highlands Coffee',
                'address' => '456 Nguyễn Đình Chiểu, Quận 3, TP.HCM',
                'phone' => '0907654321',
                'email' => 'admin@highlands-coffee.com',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
                'settings' => json_encode([
                    'currency' => 'VND',
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'tax_rate' => 10,
                    'business_type' => 'coffee_inventory',
                    'industry' => 'food_beverage',
                    'features' => [
                        'roast_date_tracking',
                        'origin_tracking',
                        'quality_grading',
                        'temperature_monitoring',
                        'blend_management',
                        'distribution_tracking'
                    ]
                ]),
                'status' => 'active'
            ]
        ];

        foreach ($stores as $storeData) {
            DB::table('stores')->updateOrInsert(
                ['slug' => $storeData['slug']],
                array_merge($storeData, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✅ Created ' . count($stores) . ' demo stores');
    }
}
