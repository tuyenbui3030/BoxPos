<?php

namespace Packages\Store\Livewire;

use Livewire\Component;
use Packages\Store\Models\Store;
use Packages\Tenant\Services\TenantService;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StoreSwitcher extends Component
{
    use Loggable;

    public ?Store $currentStore = null;
    public Collection $availableStores;
    public bool $showDropdown = false;

    protected TenantService $tenantService;

    public function boot(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function mount()
    {
        $this->loadStoreData();
    }

    public function render()
    {
        return view('store::livewire.store-switcher');
    }

    /**
     * Load current store and available stores for user
     */
    public function loadStoreData()
    {
        try {
            $this->currentStore = $this->tenantService->getCurrentStore();
            $this->availableStores = $this->tenantService->getUserStores();

            $this->logActivity('store_switcher_loaded', [
                'user_id' => Auth::id(),
                'current_store_id' => $this->currentStore?->id,
                'available_stores_count' => $this->availableStores->count(),
            ]);
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'load_store_data',
                'user_id' => Auth::id(),
            ]);

            session()->flash('error', 'Failed to load store information.');
        }
    }

    /**
     * Toggle dropdown visibility
     */
    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;

        $this->logActivity('store_switcher_dropdown_toggled', [
            'user_id' => Auth::id(),
            'dropdown_state' => $this->showDropdown ? 'opened' : 'closed',
        ]);
    }

    /**
     * Close dropdown
     */
    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    /**
     * Switch to a different store
     */
    public function switchStore(int $storeId)
    {
        try {
            $this->logActivity('store_switch_initiated', [
                'user_id' => Auth::id(),
                'current_store_id' => $this->currentStore?->id,
                'target_store_id' => $storeId,
            ]);

            // Check if user has access to the target store
            $targetStore = $this->availableStores->firstWhere('id', $storeId);

            if (!$targetStore) {
                throw new \Exception("Store not found or access denied");
            }

            // Switch store using tenant service
            $newStore = $this->tenantService->switchStore($storeId);

            // Update component state
            $this->currentStore = $newStore;
            $this->showDropdown = false;

            $this->logActivity('store_switched_successfully', [
                'user_id' => Auth::id(),
                'previous_store_id' => $this->currentStore?->id,
                'new_store_id' => $newStore->id,
                'store_name' => $newStore->name,
            ]);

            // Show success message
            session()->flash('success', "Switched to {$newStore->name}");

            // Emit event to refresh other components
            $this->dispatch('store-switched', [
                'storeId' => $newStore->id,
                'storeName' => $newStore->name,
            ]);

            // Redirect to dashboard to apply new store context
            return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'switch_store',
                'user_id' => Auth::id(),
                'target_store_id' => $storeId,
            ]);

            session()->flash('error', 'Failed to switch store: ' . $e->getMessage());
        }
    }

    /**
     * Navigate to store selection page
     */
    public function goToStoreSelection()
    {
        $this->logActivity('store_selection_navigation', [
            'user_id' => Auth::id(),
            'current_store_id' => $this->currentStore?->id,
        ]);

        return redirect()->route('store.selection');
    }

    /**
     * Navigate to store management
     */
    public function goToStoreManagement()
    {
        $this->logActivity('store_management_navigation', [
            'user_id' => Auth::id(),
            'current_store_id' => $this->currentStore?->id,
        ]);

        return redirect()->route('stores.index');
    }

    /**
     * Check if user can manage stores
     */
    public function getCanManageStoresProperty(): bool
    {
        return $this->tenantService->userHasPermission('manage_settings') ||
               $this->tenantService->isAdmin();
    }

    /**
     * Get current user's role in current store
     */
    public function getCurrentUserRoleProperty(): ?string
    {
        return $this->tenantService->getUserRole();
    }

    /**
     * Check if user has multiple stores
     */
    public function getHasMultipleStoresProperty(): bool
    {
        return $this->availableStores->count() > 1;
    }

    /**
     * Get store initials for avatar
     */
    public function getStoreInitials(Store $store): string
    {
        $words = explode(' ', $store->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($store->name, 0, 2));
    }

    /**
     * Get store status badge color
     */
    public function getStoreStatusColor(Store $store): string
    {
        return match ($store->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
            default => 'gray',
        };
    }

    /**
     * Refresh store data
     */
    public function refreshStores()
    {
        $this->loadStoreData();

        $this->logActivity('store_data_refreshed', [
            'user_id' => Auth::id(),
            'current_store_id' => $this->currentStore?->id,
        ]);

        session()->flash('success', 'Store data refreshed');
    }

    /**
     * Handle click outside to close dropdown
     */
    public function clickOutside()
    {
        $this->closeDropdown();
    }

    /**
     * Listeners for Livewire events
     */
    /**
     * Get current user's role in the current store.
     */
    public function getUserRole(): string
    {
        if (!$this->currentStore) {
            return 'N/A';
        }

        return $this->tenantService->getUserRole() ?? 'N/A';
    }

    /**
     * Check if current user can manage stores.
     */
    public function canManageStores(): bool
    {
        return $this->tenantService->userHasPermission('manage_stores') ||
               $this->tenantService->isAdmin();
    }

    protected function getListeners(): array
    {
        return [
            'store-updated' => 'refreshStores',
            'user-store-access-changed' => 'refreshStores',
            'click-outside' => 'clickOutside',
        ];
    }
}
