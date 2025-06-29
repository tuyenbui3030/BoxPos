<?php

namespace Packages\Store\Livewire;

use Livewire\Component;
use Packages\Store\Models\Store;
use Packages\Tenant\Services\TenantService;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Collection;

class StoreSelection extends Component
{
    use Loggable;

    public Collection $availableStores;
    public ?Store $selectedStore = null;
    public string $search = '';

    protected TenantService $tenantService;

    public function boot(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function mount()
    {
        $this->loadAvailableStores();
        $this->selectedStore = $this->tenantService->getCurrentStore();

        $this->logActivity('store_selection_page_viewed', [
            'user_id' => auth()->id(),
            'current_store_id' => $this->selectedStore?->id,
            'available_stores_count' => $this->availableStores->count(),
        ]);
    }

    public function render()
    {
        return view('store::livewire.store-selection', [
            'filteredStores' => $this->getFilteredStores(),
            'stores' => $this->availableStores,
        ])->layout('layouts.app');
    }

    /**
     * Load available stores for the user
     */
    public function loadAvailableStores()
    {
        try {
            $this->availableStores = $this->tenantService->getUserStores();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'load_available_stores',
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to load available stores.');
            $this->availableStores = collect();
        }
    }

    /**
     * Get filtered stores based on search
     */
    public function getFilteredStores(): Collection
    {
        if (empty($this->search)) {
            return $this->availableStores;
        }

        return $this->availableStores->filter(function ($store) {
            return str_contains(strtolower($store->name), strtolower($this->search)) ||
                   str_contains(strtolower($store->description ?? ''), strtolower($this->search));
        });
    }

    /**
     * Select and switch to a store
     */
    public function selectStore(int $storeId)
    {
        try {
            $this->logActivity('store_selection_initiated', [
                'user_id' => auth()->id(),
                'current_store_id' => $this->selectedStore?->id,
                'target_store_id' => $storeId,
            ]);

            // Find the store in available stores
            $targetStore = $this->availableStores->firstWhere('id', $storeId);
            
            if (!$targetStore) {
                throw new \Exception("Store not found or access denied");
            }

            // Switch store using tenant service
            $newStore = $this->tenantService->switchStore($storeId);
            $this->selectedStore = $newStore;

            $this->logActivity('store_selected_successfully', [
                'user_id' => auth()->id(),
                'previous_store_id' => $this->selectedStore?->id,
                'new_store_id' => $newStore->id,
                'store_name' => $newStore->name,
            ]);

            session()->flash('success', "Successfully switched to {$newStore->name}");

            // Redirect to dashboard or intended page
            $intendedUrl = session()->pull('url.intended', route('locale.dashboard', ['locale' => app()->getLocale()]));
            return redirect()->to($intendedUrl);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'select_store',
                'user_id' => auth()->id(),
                'target_store_id' => $storeId,
            ]);

            session()->flash('error', 'Failed to select store: ' . $e->getMessage());
        }
    }

    /**
     * Continue with current store
     */
    public function continueWithCurrentStore()
    {
        if (!$this->selectedStore) {
            session()->flash('error', 'No store is currently selected.');
            return;
        }

        $this->logActivity('continue_with_current_store', [
            'user_id' => auth()->id(),
            'store_id' => $this->selectedStore->id,
        ]);

        $intendedUrl = session()->pull('url.intended', route('locale.dashboard', ['locale' => app()->getLocale()]));
        return redirect()->to($intendedUrl);
    }

    /**
     * Clear search
     */
    public function clearSearch()
    {
        $this->search = '';
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
     * Get user's role in store
     */
    public function getUserRoleInStore(Store $store): string
    {
        $user = auth()->user();
        $userStore = $user->stores()->wherePivot('store_id', $store->id)->first();
        return $userStore?->pivot->role ?? 'unknown';
    }

    /**
     * Check if store is currently selected
     */
    public function isCurrentStore(Store $store): bool
    {
        return $this->selectedStore && $this->selectedStore->id === $store->id;
    }

    /**
     * Refresh available stores
     */
    public function refreshStores()
    {
        $this->loadAvailableStores();
        
        $this->logActivity('store_selection_refreshed', [
            'user_id' => auth()->id(),
            'available_stores_count' => $this->availableStores->count(),
        ]);

        session()->flash('success', 'Store list refreshed');
    }

    /**
     * Navigate to store management (if user has permission)
     */
    public function goToStoreManagement()
    {
        if (!$this->tenantService->userHasPermission('manage_settings') && !$this->tenantService->isAdmin()) {
            session()->flash('error', 'You do not have permission to manage stores.');
            return;
        }

        $this->logActivity('store_management_navigation_from_selection', [
            'user_id' => auth()->id(),
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
     * Check if user can manage a specific store
     */
    public function canManageStore($store): bool
    {
        // Check if user has admin or manager role in this store
        $userStore = auth()->user()->stores()->wherePivot('store_id', $store->id)->first();

        if (!$userStore) {
            return false;
        }

        return in_array($userStore->pivot->role, ['admin', 'manager']);
    }

    /**
     * Navigate to store management for a specific store
     */
    public function manageStore(int $storeId)
    {
        $store = $this->availableStores->firstWhere('id', $storeId);

        if (!$store || !$this->canManageStore($store)) {
            session()->flash('error', 'You do not have permission to manage this store.');
            return;
        }

        $this->logActivity('store_management_navigation', [
            'user_id' => auth()->id(),
            'store_id' => $storeId,
        ]);

        return redirect()->route('stores.show', $storeId);
    }

    /**
     * Check if user can create new stores
     */
    public function canCreateStores(): bool
    {
        return $this->tenantService->userHasPermission('manage_settings') ||
               $this->tenantService->isAdmin();
    }

    /**
     * Navigate to create new store
     */
    public function createNewStore()
    {
        if (!$this->canCreateStores()) {
            session()->flash('error', 'You do not have permission to create stores.');
            return;
        }

        $this->logActivity('store_creation_navigation', [
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('stores.create');
    }

    /**
     * Get total stores count
     */
    public function getTotalStoresProperty(): int
    {
        return $this->availableStores->count();
    }

    /**
     * Check if user has no stores
     */
    public function getHasNoStoresProperty(): bool
    {
        return $this->availableStores->isEmpty();
    }

    /**
     * Updated hook for search
     */
    public function updatedSearch()
    {
        $this->logActivity('store_selection_search', [
            'user_id' => auth()->id(),
            'search_term' => $this->search,
            'results_count' => $this->getFilteredStores()->count(),
        ]);
    }
}
