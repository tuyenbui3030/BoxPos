<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'BoxPos Demo Store',
                'slug' => 'demo001',
                'description' => 'Demo store for BoxPos system',
                'address' => '123 Nguyễn Văn Linh, Quận 7, TP.HCM',
                'phone' => '0901234567',
                'email' => 'demo@boxpos.vn',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
                'status' => 'active',
                'settings' => [
                    'pos_settings' => [
                        'auto_print_receipt' => true,
                        'require_customer' => false,
                        'allow_discount' => true,
                        'max_discount_percent' => 50,
                    ],
                    'inventory_settings' => [
                        'track_inventory' => true,
                        'allow_negative_stock' => false,
                        'low_stock_threshold' => 10,
                    ],
                    'business_hours' => [
                        'monday' => ['open' => '08:00', 'close' => '22:00'],
                        'tuesday' => ['open' => '08:00', 'close' => '22:00'],
                        'wednesday' => ['open' => '08:00', 'close' => '22:00'],
                        'thursday' => ['open' => '08:00', 'close' => '22:00'],
                        'friday' => ['open' => '08:00', 'close' => '22:00'],
                        'saturday' => ['open' => '08:00', 'close' => '23:00'],
                        'sunday' => ['open' => '09:00', 'close' => '21:00'],
                    ],
                ],
            ],
            [
                'name' => 'BoxPos Branch 2',
                'slug' => 'branch2',
                'description' => 'Second branch of BoxPos',
                'address' => '456 Lê Văn Việt, Quận 9, TP.HCM',
                'phone' => '0907654321',
                'email' => 'branch2@boxpos.vn',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
                'status' => 'active',
                'settings' => [
                    'pos_settings' => [
                        'auto_print_receipt' => true,
                        'require_customer' => true,
                        'allow_discount' => true,
                        'max_discount_percent' => 30,
                    ],
                    'inventory_settings' => [
                        'track_inventory' => true,
                        'allow_negative_stock' => true,
                        'low_stock_threshold' => 5,
                    ],
                ],
            ], 
        ];

        foreach ($stores as $storeData) {
            Store::create($storeData);
        }
    }
}
