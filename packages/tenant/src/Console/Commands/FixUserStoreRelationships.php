<?php

namespace Packages\Tenant\Console\Commands;

use Illuminate\Console\Command;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class FixUserStoreRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:fix-user-stores
                            {--dry-run : Show what would be done without making changes}
                            {--user= : Fix specific user by ID}
                            {--all-stores : Assign users to all available stores instead of just default store}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix users without store relationships to prevent redirect loops';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $specificUserId = $this->option('user');
        $assignAllStores = $this->option('all-stores');

        if ($assignAllStores) {
            return $this->handleAssignAllStores($isDryRun, $specificUserId);
        }

        $this->info('Checking for users without store relationships...');

        // Get users without stores
        $query = User::whereDoesntHave('stores');

        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }

        $usersWithoutStores = $query->get();

        if ($usersWithoutStores->isEmpty()) {
            $this->info('✅ All users have store relationships.');
            return 0;
        }

        $this->warn("Found {$usersWithoutStores->count()} users without store relationships:");

        // Get the first available store
        $defaultStore = Store::where('status', Store::STATUS_ACTIVE)->first();

        if (!$defaultStore) {
            $this->error('❌ No active stores found. Please create at least one active store first.');
            return 1;
        }

        $this->info("Default store: {$defaultStore->name} (ID: {$defaultStore->id})");
        $this->newLine();

        foreach ($usersWithoutStores as $user) {
            $this->line("User: {$user->name} ({$user->email}) - ID: {$user->id}");
            
            if ($isDryRun) {
                $this->line("  [DRY RUN] Would assign to store: {$defaultStore->name}");
                $this->line("  [DRY RUN] Would set current_store_id: {$defaultStore->id}");
            } else {
                try {
                    // Assign user to default store with admin role
                    $user->stores()->attach($defaultStore->id, [
                        'role' => 'admin',
                        'permissions' => json_encode([
                            'manage_settings',
                            'manage_customers', 
                            'manage_products',
                            'view_dashboard'
                        ]),
                        'is_active' => true,
                        'joined_at' => now(),
                    ]);

                    // Set current store
                    $user->update(['current_store_id' => $defaultStore->id]);

                    $this->line("  ✅ Assigned to store: {$defaultStore->name}");
                } catch (\Exception $e) {
                    $this->line("  ❌ Failed: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info('This was a dry run. Use without --dry-run to apply changes.');
        } else {
            $this->info('✅ User-store relationships have been fixed.');
        }

        return 0;
    }

    /**
     * Handle assigning users to all available stores
     */
    protected function handleAssignAllStores($isDryRun, $specificUserId)
    {
        $this->info('Assigning users to all available stores...');

        // Get all active stores
        $stores = Store::where('status', Store::STATUS_ACTIVE)->get();

        if ($stores->isEmpty()) {
            $this->error('❌ No active stores found.');
            return 1;
        }

        $this->info("Found {$stores->count()} active stores:");
        foreach ($stores as $store) {
            $this->line("  - {$store->name} (ID: {$store->id})");
        }
        $this->newLine();

        // Get users to process
        $query = User::query();
        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('No users found to process.');
            return 0;
        }

        foreach ($users as $user) {
            $this->line("Processing user: {$user->name} ({$user->email})");

            foreach ($stores as $store) {
                // Check if user already has access to this store
                $hasAccess = $user->stores()->where('store_id', $store->id)->exists();

                if ($hasAccess) {
                    $this->line("  ✓ Already has access to: {$store->name}");
                    continue;
                }

                if ($isDryRun) {
                    $this->line("  [DRY RUN] Would assign to: {$store->name}");
                } else {
                    try {
                        // Assign user to store
                        $user->stores()->attach($store->id, [
                            'role' => 'manager',
                            'permissions' => json_encode([
                                'manage_customers',
                                'manage_products',
                                'view_dashboard'
                            ]),
                            'is_active' => true,
                            'joined_at' => now(),
                        ]);

                        $this->line("  ✅ Assigned to: {$store->name}");
                    } catch (\Exception $e) {
                        $this->line("  ❌ Failed to assign to {$store->name}: {$e->getMessage()}");
                    }
                }
            }

            // Set first store as current if user doesn't have one
            if (!$isDryRun && !$user->current_store_id) {
                $firstStore = $stores->first();
                $user->update(['current_store_id' => $firstStore->id]);
                $this->line("  ✅ Set current store to: {$firstStore->name}");
            }

            $this->newLine();
        }

        if ($isDryRun) {
            $this->info('This was a dry run. Use without --dry-run to apply changes.');
        } else {
            $this->info('✅ Users have been assigned to all available stores.');
        }

        return 0;
    }
}
