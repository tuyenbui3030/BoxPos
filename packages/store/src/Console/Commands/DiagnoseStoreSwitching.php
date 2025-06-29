<?php

namespace Packages\Store\Console\Commands;

use Illuminate\Console\Command;
use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Packages\Tenant\Services\TenantService;
use Illuminate\Support\Facades\Route;

class DiagnoseStoreSwitching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:diagnose-switching {--user=1 : User ID to diagnose}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose store switching issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("=== STORE SWITCHING DIAGNOSIS ===");
        $this->info("User: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Check 1: User has stores
        $this->info("1. Checking user store access...");
        $userStores = $user->stores;
        if ($userStores->isEmpty()) {
            $this->error("❌ User has no store access!");
            $this->line("Fix: Run 'php artisan tenant:fix-user-stores --all-stores'");
            return 1;
        }
        $this->line("✅ User has access to {$userStores->count()} stores");
        foreach ($userStores as $store) {
            $this->line("   - {$store->name} (ID: {$store->id}) - Role: {$store->pivot->role}");
        }
        $this->newLine();

        // Check 2: Routes exist
        $this->info("2. Checking required routes...");
        $requiredRoutes = [
            'locale.dashboard',
            'store.selection',
            'store.switch',
        ];

        foreach ($requiredRoutes as $routeName) {
            if (Route::has($routeName)) {
                $this->line("✅ Route '{$routeName}' exists");
            } else {
                $this->error("❌ Route '{$routeName}' missing!");
            }
        }
        $this->newLine();

        // Check 3: TenantService functionality
        $this->info("3. Testing TenantService...");
        auth()->login($user);
        
        $tenantService = app(TenantService::class);
        $currentStore = $tenantService->getCurrentStore();
        $this->line("Current store: " . ($currentStore?->name ?? 'None'));

        if ($userStores->count() > 1) {
            $targetStore = $userStores->where('id', '!=', $currentStore?->id)->first();
            try {
                $newStore = $tenantService->switchStore($targetStore->id);
                $this->line("✅ TenantService switch successful: {$newStore->name}");
            } catch (\Exception $e) {
                $this->error("❌ TenantService switch failed: {$e->getMessage()}");
            }
        }
        $this->newLine();

        // Check 4: Database integrity
        $this->info("4. Checking database integrity...");
        
        // Check user_stores table
        $userStoreCount = \DB::table('user_stores')->where('user_id', $user->id)->count();
        $this->line("User-store relationships in DB: {$userStoreCount}");
        
        // Check current_store_id
        $this->line("User current_store_id: " . ($user->current_store_id ?? 'NULL'));
        
        if ($user->current_store_id) {
            $currentStoreExists = Store::find($user->current_store_id);
            if ($currentStoreExists) {
                $this->line("✅ Current store exists in database");
            } else {
                $this->error("❌ Current store ID points to non-existent store!");
            }
        }
        $this->newLine();

        // Check 5: Permissions
        $this->info("5. Checking permissions...");
        foreach ($userStores as $store) {
            $permissions = json_decode($store->pivot->permissions, true);
            $this->line("Store '{$store->name}' permissions: " . implode(', ', $permissions ?? []));
        }
        $this->newLine();

        // Check 6: Common issues
        $this->info("6. Checking for common issues...");
        
        // Check for inactive relationships
        $inactiveCount = $user->stores()->wherePivot('is_active', false)->count();
        if ($inactiveCount > 0) {
            $this->warn("⚠️  {$inactiveCount} inactive store relationships found");
        } else {
            $this->line("✅ All store relationships are active");
        }

        // Check for duplicate relationships
        $duplicates = \DB::table('user_stores')
            ->select('user_id', 'store_id', \DB::raw('COUNT(*) as count'))
            ->where('user_id', $user->id)
            ->groupBy('user_id', 'store_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->warn("⚠️  Duplicate user-store relationships found");
            foreach ($duplicates as $duplicate) {
                $this->line("   User {$duplicate->user_id} - Store {$duplicate->store_id}: {$duplicate->count} relationships");
            }
        } else {
            $this->line("✅ No duplicate relationships found");
        }

        $this->newLine();
        $this->info("=== DIAGNOSIS COMPLETE ===");
        
        if ($userStores->count() < 2) {
            $this->warn("Note: User only has {$userStores->count()} store(s). Store switching requires at least 2 stores.");
            $this->line("To add more stores: php artisan tenant:fix-user-stores --all-stores");
        }

        return 0;
    }
}
