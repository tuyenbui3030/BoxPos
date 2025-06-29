<?php

namespace Packages\Tenant\Console\Commands;

use Illuminate\Console\Command;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class CheckUserStoreAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:check-user-access 
                            {--user= : Check specific user by ID}
                            {--store= : Check specific store by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user-store access relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $specificUserId = $this->option('user');
        $specificStoreId = $this->option('store');

        $this->info('=== USER-STORE ACCESS REPORT ===');
        $this->newLine();

        // Get stores
        $stores = Store::all();
        $this->info("Total stores in system: {$stores->count()}");
        foreach ($stores as $store) {
            $this->line("  - {$store->name} (ID: {$store->id}) - Status: {$store->status}");
        }
        $this->newLine();

        // Get users
        $query = User::with('stores');
        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return 0;
        }

        $this->info("Checking {$users->count()} users:");
        $this->newLine();

        foreach ($users as $user) {
            $this->line("👤 User: {$user->name} ({$user->email}) - ID: {$user->id}");
            $this->line("   Current Store ID: " . ($user->current_store_id ?? 'None'));
            
            $userStores = $user->stores;
            if ($userStores->isEmpty()) {
                $this->line("   ❌ No store access");
            } else {
                $this->line("   ✅ Has access to {$userStores->count()} stores:");
                foreach ($userStores as $store) {
                    $pivot = $store->pivot;
                    $status = $pivot->is_active ? '✅ Active' : '❌ Inactive';
                    $this->line("      - {$store->name} (ID: {$store->id}) - Role: {$pivot->role} - {$status}");
                    
                    if ($pivot->permissions) {
                        $permissions = json_decode($pivot->permissions, true);
                        if (is_array($permissions) && !empty($permissions)) {
                            $this->line("        Permissions: " . implode(', ', $permissions));
                        }
                    }
                }
            }
            $this->newLine();
        }

        // Summary statistics
        $this->info('=== SUMMARY ===');
        $totalUsers = $users->count();
        $usersWithStores = $users->filter(fn($user) => $user->stores->count() > 0)->count();
        $usersWithoutStores = $totalUsers - $usersWithStores;
        
        $this->line("Total users: {$totalUsers}");
        $this->line("Users with store access: {$usersWithStores}");
        $this->line("Users without store access: {$usersWithoutStores}");
        
        if ($usersWithoutStores > 0) {
            $this->newLine();
            $this->warn("⚠️  {$usersWithoutStores} users don't have store access!");
            $this->line("Run: php artisan tenant:fix-user-stores");
        }

        // Store coverage
        $this->newLine();
        $this->info('=== STORE COVERAGE ===');
        foreach ($stores as $store) {
            $userCount = $store->users()->count();
            $this->line("🏪 {$store->name}: {$userCount} users have access");
        }

        return 0;
    }
}
