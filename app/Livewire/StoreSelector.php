<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Packages\Store\Services\StoreService;
use Packages\Store\Models\Store;

class StoreSelector extends Component
{
    use Loggable;

    public $showDropdown = false;
    public $search = '';
    public $currentStore = null;
    public $availableStores = [];
    public $mobile = false;

    protected $listeners = [
        'store-switched' => 'handleStoreSwitch'
    ];

    public function mount($mobile = false)
    {
        $this->mobile = $mobile;
        $this->logActivity('store_selector_mounted', [
            'mobile' => $this->mobile
        ]);
        $this->loadStores();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        
        if ($this->showDropdown) {
            $this->loadStores();
            $this->logActivity('store_selector_opened');
        } else {
            $this->logActivity('store_selector_closed');
        }
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
        $this->search = '';
        $this->logActivity('store_selector_closed');
    }

    public function switchStore($storeId)
    {
        try {
            $storeService = app(StoreService::class);
            
            if (!$storeService->switchStore($storeId)) {
                $this->logError(new \Exception("Failed to switch to store {$storeId}"), [
                    'attempted_store_id' => $storeId
                ]);
                
                $this->dispatch('store-switch-failed', [
                    'message' => 'Unable to switch to the selected store. Please try again.'
                ]);
                return;
            }

            $this->logActivity('store_switched', [
                'previous_store_id' => $this->currentStore?->id,
                'new_store_id' => $storeId
            ]);

            $this->loadStores();
            $this->closeDropdown();

            // Dispatch events to update other components
            $this->dispatch('store-switched', $storeId);
            
            // Refresh the page to ensure all components are updated with new store context
            $this->js('window.location.reload()');
            
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'switch_store',
                'store_id' => $storeId
            ]);
            
            $this->dispatch('store-switch-failed', [
                'message' => 'An error occurred while switching stores. Please try again.'
            ]);
        }
    }

    public function updatedSearch()
    {
        $this->logActivity('store_search_updated', [
            'search_term' => $this->search
        ]);
    }

    public function handleStoreSwitch($storeId)
    {
        $this->loadStores();
    }

    protected function loadStores()
    {
        if (!auth()->check()) {
            return;
        }

        try {
            $user = auth()->user();
            
            // Get current store
            $this->currentStore = $user->currentStore;
            
            // Get available stores for user with search filter
            $query = $user->activeStores()
                ->select('id', 'name', 'logo', 'address');
                
            if (!empty($this->search)) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }
            
            $this->availableStores = $query->get()->toArray();

            $this->logActivity('stores_loaded', [
                'current_store_id' => $this->currentStore?->id,
                'available_stores_count' => count($this->availableStores),
                'search_term' => $this->search
            ]);
            
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'load_stores'
            ]);
            
            $this->availableStores = [];
            $this->currentStore = null;
        }
    }

    public function getFilteredStoresProperty()
    {
        if (empty($this->search)) {
            return $this->availableStores;
        }

        return array_filter($this->availableStores, function ($store) {
            return stripos($store['name'], $this->search) !== false;
        });
    }

    public function render()
    {
        return view('livewire.store-selector');
    }
}