<?php

namespace Packages\User\Database\Seeders;

use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder assigns users to stores and sets up proper tenant relationships.
     * It should run after UserSeeder and StoreSeeder.
     */
    public function run(): void
    {
        $this->command->info('Setting up user-store relationships...');

        // Get all users and stores
        $users = User::all();
        $stores = Store::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($stores->isEmpty()) {
            $this->command->warn('No stores found. Please run StoreSeeder first.');
            return;
        }

        $this->command->info("Found {$users->count()} users and {$stores->count()} stores");

        // Clear existing user-store relationships to avoid duplicates
        DB::table('user_stores')->truncate();

        foreach ($users as $user) {
            $this->assignUserToStores($user, $stores);
        }

        $this->command->info('User-store relationships created successfully!');
    }

    /**
     * Assign a user to stores with appropriate roles and permissions
     */
    private function assignUserToStores(User $user, $stores): void
    {
        $isAdmin = str_contains($user->email, 'admin');
        
        // Determine role and permissions based on user type
        $role = $isAdmin ? 'admin' : 'staff';
        $permissions = $this->getPermissionsForRole($role);

        foreach ($stores as $store) {
            // Attach user to store with pivot data
            $user->stores()->attach($store->id, [
                'role' => $role,
                'permissions' => json_encode($permissions),
                'is_active' => true,
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->line("  - Assigned {$user->email} to {$store->name} as {$role}");
        }

        // Set the first store as the user's current store
        if (!$user->current_store_id) {
            $user->current_store_id = $stores->first()->id;
            $user->save();
            
            $this->command->line("  - Set current store for {$user->email}: {$stores->first()->name}");
        }
    }

    /**
     * Get permissions array based on role
     */
    private function getPermissionsForRole(string $role): array
    {
        return match ($role) {
            'admin' => [
                'manage_settings',
                'manage_customers', 
                'manage_products',
                'manage_users',
                'view_dashboard',
                'manage_orders',
                'view_reports',
            ],
            'manager' => [
                'manage_customers',
                'manage_products', 
                'view_dashboard',
                'manage_orders',
                'view_reports',
            ],
            'staff' => [
                'view_dashboard',
                'manage_customers',
                'manage_orders',
            ],
            default => ['view_dashboard'],
        };
    }
}
