<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Customer\Models\Customer;
use Illuminate\Support\Facades\Hash;

class MultiTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create stores
        $store1 = Store::create([
            'name' => 'BoxPos Dashboard',
            'slug' => 'boxpos-dashboard',
            'email' => 'admin@boxpos.com',
            'phone' => '+1234567890',
            'address' => '123 Business Center, Tech City',
            'description' => 'Main dashboard for business management and analytics',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'language' => 'en',
            'status' => 'active'
        ]);

        $store2 = Store::create([
            'name' => 'Coffee Bean Inventory',
            'slug' => 'coffee-bean-inventory',
            'email' => 'inventory@coffeebean.com',
            'phone' => '+1234567891',
            'address' => '456 Coffee Street, Bean Town',
            'description' => 'Coffee shop inventory management system',
            'timezone' => 'UTC',
            'currency' => 'USD',
            'language' => 'en',
            'status' => 'active'
        ]);

        // Create users
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@boxpos.com',
            'password' => Hash::make('password'),
            'current_store_id' => $store1->id,
        ]);

        $manager = User::create([
            'name' => 'Store Manager',
            'email' => 'manager@boxpos.com',
            'password' => Hash::make('password'),
            'current_store_id' => $store1->id,
        ]);

        $staff = User::create([
            'name' => 'Store Staff',
            'email' => 'staff@boxpos.com',
            'password' => Hash::make('password'),
            'current_store_id' => $store2->id,
        ]);

        // Assign users to stores with roles

        // Admin has access to all stores as admin
        $admin->stores()->attach($store1->id, [
            'role' => 'admin',
            'permissions' => json_encode([
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
                'manage_settings',
                'manage_users',
            ]),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $admin->stores()->attach($store2->id, [
            'role' => 'admin',
            'permissions' => json_encode([
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
                'manage_settings',
                'manage_users',
            ]),
            'is_active' => true,
            'joined_at' => now(),
        ]);



        // Manager has access to store1 and store2 as manager
        $manager->stores()->attach($store1->id, [
            'role' => 'manager',
            'permissions' => json_encode([
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
            ]),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $manager->stores()->attach($store2->id, [
            'role' => 'manager',
            'permissions' => json_encode([
                'view_dashboard',
                'manage_customers',
                'manage_products',
                'manage_orders',
                'manage_inventory',
                'manage_reports',
            ]),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        // Staff has access to store2 as staff
        $staff->stores()->attach($store2->id, [
            'role' => 'staff',
            'permissions' => json_encode([
                'view_dashboard',
                'manage_customers',
                'manage_orders',
                'manage_inventory',
            ]),
            'is_active' => true,
            'joined_at' => now(),
        ]);

        // Create sample customers for each store

        // Dashboard Management customers
        Customer::create([
            'store_id' => $store1->id,
            'customer_code' => 'DASH001',
            'customer_name' => 'John Business',
            'email' => 'john@business.com',
            'phone_number' => '+1234567890',
            'address' => '123 Corporate St',
            'customer_type' => 'company',
            'gender' => 'male',
            'customer_group' => 'enterprise',
            'created_by' => $admin->id,
        ]);

        Customer::create([
            'store_id' => $store1->id,
            'customer_code' => 'DASH002',
            'customer_name' => 'Jane Analytics',
            'email' => 'jane@analytics.com',
            'phone_number' => '+1234567891',
            'address' => '456 Data Ave',
            'customer_type' => 'company',
            'gender' => 'female',
            'customer_group' => 'premium',
            'created_by' => $manager->id,
        ]);

        // Coffee Shop Inventory customers
        Customer::create([
            'store_id' => $store2->id,
            'customer_code' => 'COFFEE001',
            'customer_name' => 'Alice Barista',
            'email' => 'alice@coffeebean.com',
            'phone_number' => '+1234567892',
            'address' => '789 Coffee Blvd',
            'customer_type' => 'individual',
            'gender' => 'female',
            'customer_group' => 'regular',
            'created_by' => $staff->id,
        ]);

        Customer::create([
            'store_id' => $store2->id,
            'customer_code' => 'COFFEE002',
            'customer_name' => 'Bob Roaster',
            'email' => 'bob@roaster.com',
            'phone_number' => '+1234567893',
            'address' => '321 Bean St',
            'customer_type' => 'company',
            'gender' => 'male',
            'customer_group' => 'supplier',
            'created_by' => $staff->id,
        ]);

        $this->command->info('Multi-tenant sample data created successfully!');
        $this->command->info('Stores created: ' . Store::count());
        $this->command->info('Users created: ' . User::count());
        $this->command->info('Customers created: ' . Customer::count());
        $this->command->info('');
        $this->command->info('Sample Projects:');
        $this->command->info('1. BoxPos Dashboard - Business management and analytics');
        $this->command->info('2. Coffee Bean Inventory - Coffee shop inventory system');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@boxpos.com / password (access to both projects)');
        $this->command->info('Manager: manager@boxpos.com / password (access to both projects)');
        $this->command->info('Staff: staff@boxpos.com / password (access to coffee inventory)');
        $this->command->info('');
        $this->command->info('Alternative login: admin@example.com / password');
    }
}
