<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Packages\Store\Models\UserStore;

class MultiStoreUserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo thêm một số store nếu chưa có đủ
        $this->ensureMultipleStores();

        // Tạo super admin user có thể quản lý tất cả stores
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@boxpos.vn',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_store_id' => Store::first()->id, // Set store đầu tiên làm current
        ]);

        // Gán super admin vào tất cả stores với role admin
        $stores = Store::all();
        foreach ($stores as $store) {
            UserStore::create([
                'user_id' => $superAdmin->id,
                'store_id' => $store->id,
                'role' => 'admin',
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => ['*'], // Admin có tất cả permissions
            ]);
        }

        // Tạo multi-store manager (quản lý 2-3 stores)
        $multiManager = User::create([
            'name' => 'Multi Store Manager',
            'email' => 'multimanager@boxpos.vn',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_store_id' => Store::first()->id,
        ]);

        // Gán manager vào 3 stores đầu tiên
        $managedStores = $stores->take(3);
        foreach ($managedStores as $store) {
            UserStore::create([
                'user_id' => $multiManager->id,
                'store_id' => $store->id,
                'role' => 'manager',
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => [
                    'sales.view', 'sales.create', 'sales.edit',
                    'inventory.view', 'inventory.edit',
                    'customers.view', 'customers.create', 'customers.edit',
                    'reports.view', 'reports.create',
                    'employees.view', 'employees.create',
                ],
            ]);
        }

        // Tạo regional supervisor (giám sát nhiều stores nhưng không phải admin)
        $supervisor = User::create([
            'name' => 'Regional Supervisor',
            'email' => 'supervisor@boxpos.vn',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'current_store_id' => Store::first()->id,
        ]);

        // Gán supervisor vào tất cả stores với role manager
        foreach ($stores as $store) {
            UserStore::create([
                'user_id' => $supervisor->id,
                'store_id' => $store->id,
                'role' => 'manager',
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => [
                    'sales.view', 'sales.create',
                    'inventory.view',
                    'customers.view',
                    'reports.view',
                    'employees.view',
                ],
            ]);
        }

        $this->command->info('✅ Multi-store users created successfully!');
        $this->command->info('📋 Created users:');
        $this->command->info("   🔑 Super Admin: superadmin@boxpos.vn / password (Admin in {$stores->count()} stores)");
        $this->command->info("   🔑 Multi Manager: multimanager@boxpos.vn / password (Manager in 3 stores)");
        $this->command->info("   🔑 Regional Supervisor: supervisor@boxpos.vn / password (Manager in {$stores->count()} stores)");
    }

    /**
     * Đảm bảo có ít nhất 4 stores để test multi-store functionality
     */
    private function ensureMultipleStores(): void
    {
        $currentStoreCount = Store::count();
        
        if ($currentStoreCount < 4) {
            $additionalStores = [
                [
                    'name' => 'BoxPos Branch 3',
                    'slug' => 'branch3',
                    'description' => 'Third branch of BoxPos',
                    'address' => '789 Võ Văn Tần, Quận 3, TP.HCM',
                    'phone' => '0908123456',
                    'email' => 'branch3@boxpos.vn',
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'currency' => 'VND',
                    'language' => 'vi',
                    'status' => 'active',
                    'settings' => [
                        'pos_settings' => [
                            'auto_print_receipt' => true,
                            'require_customer' => false,
                            'allow_discount' => true,
                            'max_discount_percent' => 40,
                        ],
                        'inventory_settings' => [
                            'track_inventory' => true,
                            'allow_negative_stock' => false,
                            'low_stock_threshold' => 15,
                        ],
                    ],
                ],
                [
                    'name' => 'BoxPos Branch 4',
                    'slug' => 'branch4',
                    'description' => 'Fourth branch of BoxPos',
                    'address' => '321 Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
                    'phone' => '0909876543',
                    'email' => 'branch4@boxpos.vn',
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'currency' => 'VND',
                    'language' => 'vi',
                    'status' => 'active',
                    'settings' => [
                        'pos_settings' => [
                            'auto_print_receipt' => false,
                            'require_customer' => true,
                            'allow_discount' => true,
                            'max_discount_percent' => 25,
                        ],
                        'inventory_settings' => [
                            'track_inventory' => true,
                            'allow_negative_stock' => true,
                            'low_stock_threshold' => 8,
                        ],
                    ],
                ],
            ];

            $storesToCreate = 4 - $currentStoreCount;
            for ($i = 0; $i < $storesToCreate && $i < count($additionalStores); $i++) {
                Store::create($additionalStores[$i]);
            }

            $this->command->info("✅ Created {$storesToCreate} additional stores for multi-store testing");
        }
    }
}