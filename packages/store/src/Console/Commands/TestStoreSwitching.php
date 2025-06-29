<?php

namespace Packages\Store\Console\Commands;

use Illuminate\Console\Command;
use Packages\User\Models\User;
use Packages\Store\Livewire\StoreSelection;
use Packages\Tenant\Services\TenantService;
use Livewire\Livewire;

class TestStoreSwitching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:test-switching {--user=1 : User ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test store switching functionality end-to-end';

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

        $this->info("Testing store switching with user: {$user->name}");
        $this->newLine();

        // Simulate login
        auth()->login($user);

        $tenantService = app(TenantService::class);
        $userStores = $tenantService->getUserStores();

        if ($userStores->count() < 2) {
            $this->warn("User only has {$userStores->count()} stores. Need at least 2 stores to test switching.");
            return 0;
        }

        $this->info("=== Initial State ===");
        $currentStore = $tenantService->getCurrentStore();
        $this->line("Current store: " . ($currentStore?->name ?? 'None'));
        $this->line("Available stores: {$userStores->count()}");
        foreach ($userStores as $store) {
            $this->line("  - {$store->name} (ID: {$store->id})");
        }
        $this->newLine();

        // Test switching to different store
        $targetStore = $userStores->where('id', '!=', $currentStore?->id)->first();
        
        if (!$targetStore) {
            $this->warn("No different store found to switch to.");
            return 0;
        }

        $this->info("=== Testing Store Switch ===");
        $this->line("Switching from: " . ($currentStore?->name ?? 'None'));
        $this->line("Switching to: {$targetStore->name} (ID: {$targetStore->id})");

        try {
            // Test via Livewire component
            $component = Livewire::test(StoreSelection::class);
            
            $this->line("Component loaded successfully");
            $this->line("Available stores in component: " . $component->get('availableStores')->count());
            
            // Test selectStore method
            $response = $component->call('selectStore', $targetStore->id);
            
            if ($response->effects['redirect'] ?? false) {
                $this->line("✅ Component triggered redirect");
                $redirectUrl = $response->effects['redirect'];
                $this->line("Redirect URL: {$redirectUrl}");
            } else {
                $this->line("❌ No redirect triggered");
            }

            // Check if store was actually switched
            $newCurrentStore = $tenantService->getCurrentStore();
            if ($newCurrentStore && $newCurrentStore->id === $targetStore->id) {
                $this->line("✅ Store successfully switched to: {$newCurrentStore->name}");
            } else {
                $this->line("❌ Store switch failed. Current store: " . ($newCurrentStore?->name ?? 'None'));
            }

        } catch (\Exception $e) {
            $this->error("❌ Store switching test failed: {$e->getMessage()}");
            $this->line("Stack trace: {$e->getTraceAsString()}");
            return 1;
        }

        $this->newLine();
        $this->info("=== Final State ===");
        $finalStore = $tenantService->getCurrentStore();
        $this->line("Final current store: " . ($finalStore?->name ?? 'None'));

        $this->newLine();
        $this->info("✅ Store switching test completed!");

        return 0;
    }
}
