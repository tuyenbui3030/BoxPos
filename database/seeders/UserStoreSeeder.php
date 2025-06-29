<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class UserStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding user-store relationships...');

        // Get all users without stores
        $usersWithoutStores = User::whereDoesntHave('stores')->get();

        if ($usersWithoutStores->isEmpty()) {
            $this->command->info('All users already have store relationships.');
            return;
        }

        // Get the first active store
        $defaultStore = Store::where('status', Store::STATUS_ACTIVE)->first();

        if (!$defaultStore) {
            $this->command->warn('No active stores found. Creating a default store...');
            
            $defaultStore = Store::create([
                'name' => 'Default Store',
                'slug' => 'default-store',
                'status' => Store::STATUS_ACTIVE,
                'description' => 'Default store created by seeder',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'currency' => 'VND',
                'language' => 'vi',
            ]);
        }

        $this->command->info("Using store: {$defaultStore->name} (ID: {$defaultStore->id})");

        foreach ($usersWithoutStores as $user) {
            // Assign user to default store
            $user->stores()->attach($defaultStore->id, [
                'role' => 'admin',
                'permissions' => json_encode([
                    'manage_settings',
                    'manage_customers',
                    'manage_products',
                    'view_dashboard',
                    'manage_users',
                ]),
                'is_active' => true,
                'joined_at' => now(),
            ]);

            // Set current store
            $user->update(['current_store_id' => $defaultStore->id]);

            $this->command->line("✅ Assigned {$user->name} to {$defaultStore->name}");
        }

        $this->command->info("✅ Seeded {$usersWithoutStores->count()} user-store relationships.");
    }
}
