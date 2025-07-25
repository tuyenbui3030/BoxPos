<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Packages\Store\Models\UserStore;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            // Admin user
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@' . strtolower($store->slug) . '.boxpos.vn',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'current_store_id' => $store->id,
            ]);

            // Create user-store relationship for admin
            UserStore::create([
                'user_id' => $admin->id,
                'store_id' => $store->id,
                'role' => 'admin',
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => ['*'], // Admin has all permissions
            ]);

            // Manager user
            $manager = User::create([
                'name' => 'Manager User',
                'email' => 'manager@' . strtolower($store->slug) . '.boxpos.vn',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'current_store_id' => $store->id,
            ]);

            // Create user-store relationship for manager
            UserStore::create([
                'user_id' => $manager->id,
                'store_id' => $store->id,
                'role' => 'manager',
                'is_active' => true,
                'joined_at' => now(),
                'permissions' => [
                    'sales.view', 'sales.create', 'sales.edit',
                    'inventory.view', 'inventory.edit',
                    'customers.view', 'customers.create', 'customers.edit',
                    'reports.view',
                ],
            ]);

            // Cashier users
            for ($i = 1; $i <= 3; $i++) {
                $cashier = User::create([
                    'name' => "Cashier {$i}",
                    'email' => "cashier{$i}@" . strtolower($store->slug) . '.boxpos.vn',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'current_store_id' => $store->id,
                ]);

                // Create user-store relationship for cashier
                UserStore::create([
                    'user_id' => $cashier->id,
                    'store_id' => $store->id,
                    'role' => 'staff',
                    'is_active' => true,
                    'joined_at' => now(),
                    'permissions' => [
                        'sales.view', 'sales.create',
                        'customers.view', 'customers.create',
                    ],
                ]);
            }

            // Sales staff
            for ($i = 1; $i <= 2; $i++) {
                $sales = User::create([
                    'name' => "Sales Staff {$i}",
                    'email' => "sales{$i}@" . strtolower($store->slug) . '.boxpos.vn',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'current_store_id' => $store->id,
                ]);

                // Create user-store relationship for sales staff
                UserStore::create([
                    'user_id' => $sales->id,
                    'store_id' => $store->id,
                    'role' => 'staff',
                    'is_active' => true,
                    'joined_at' => now(),
                    'permissions' => [
                        'sales.view', 'sales.create', 'sales.edit',
                        'customers.view', 'customers.create', 'customers.edit',
                        'inventory.view',
                    ],
                ]);
            }
        }
    }
}
