<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo users demo cho từng store
     */
    public function run(): void
    {
        // Lấy danh sách stores
        $stores = DB::table('stores')->get();

        foreach ($stores as $store) {
            // Tạo admin user cho mỗi store
            $adminEmail = 'admin@' . $store->slug . '.com';
            $adminUser = DB::table('users')->where('email', $adminEmail)->first();

            if (!$adminUser) {
                $adminUserId = DB::table('users')->insertGetId([
                    'name' => 'Admin ' . $store->name,
                    'email' => $adminEmail,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'current_store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $adminUserId = $adminUser->id;
            }

            // Tạo manager user
            $managerEmail = 'manager@' . $store->slug . '.com';
            $managerUser = DB::table('users')->where('email', $managerEmail)->first();

            if (!$managerUser) {
                $managerUserId = DB::table('users')->insertGetId([
                    'name' => 'Manager ' . $store->name,
                    'email' => $managerEmail,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'current_store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $managerUserId = $managerUser->id;
            }

            // Tạo staff user
            $staffEmail = 'staff@' . $store->slug . '.com';
            $staffUser = DB::table('users')->where('email', $staffEmail)->first();

            if (!$staffUser) {
                $staffUserId = DB::table('users')->insertGetId([
                    'name' => 'Staff ' . $store->name,
                    'email' => $staffEmail,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'current_store_id' => $store->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $staffUserId = $staffUser->id;
            }

            // Liên kết users với store
            $userStoreData = [
                ['user_id' => $adminUserId, 'store_id' => $store->id, 'role' => 'admin', 'is_active' => true],
                ['user_id' => $managerUserId, 'store_id' => $store->id, 'role' => 'manager', 'is_active' => true],
                ['user_id' => $staffUserId, 'store_id' => $store->id, 'role' => 'staff', 'is_active' => true],
            ];

            foreach ($userStoreData as $data) {
                DB::table('user_stores')->updateOrInsert(
                    ['user_id' => $data['user_id'], 'store_id' => $data['store_id']],
                    array_merge($data, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );
            }

            $this->command->info("✅ Created users for store: {$store->name}");
        }

        $this->command->info('✅ All demo users created successfully!');
        $this->command->info('📧 Login credentials: admin@[store-slug].com / password');
    }
}
