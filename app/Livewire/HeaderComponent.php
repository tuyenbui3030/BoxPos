<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Packages\Store\Models\Store;

class HeaderComponent extends Component
{
    use Loggable;

    public $currentStore = null;
    public $availableStores = [];
    protected $listeners = [
        'store-switched' => 'handleStoreSwitch'
    ];

    public function mount()
    {
        $this->logActivity('header_component_mounted');
        $this->loadStoreData();
    }

    public function handleStoreSwitch($storeId)
    {
        $this->logActivity('store_switch_handled', [
            'new_store_id' => $storeId
        ]);
        $this->loadStoreData();
    }

    protected function loadStoreData()
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Get current store
            $this->currentStore = $user->currentStore;
            
            // Get available stores for user
            $this->availableStores = $user->activeStores()
                ->select('stores.id', 'stores.name', 'stores.logo')
                ->get()
                ->toArray();

            $this->logActivity('store_data_loaded', [
                'current_store_id' => $this->currentStore?->id,
                'available_stores_count' => count($this->availableStores)
            ]);
        }
    }

    public function getLogoUrlProperty()
    {
        if ($this->currentStore && $this->currentStore->logo) {
            return asset('storage/' . $this->currentStore->logo);
        }
        
        return asset('images/boxpos-logo.png');
    }

    public function getStoreNameProperty()
    {
        return $this->currentStore?->name ?? config('app.name', 'BoxPos');
    }

    /**
     * Build localized URL for language switching
     */
    public function buildLocalizedUrl(string $currentPath, string $locale): string
    {
        // Remove current locale from path if exists
        $cleanPath = $currentPath;
        if (preg_match('/^(vi|en)\/(.*)$/', $currentPath, $matches)) {
            $cleanPath = $matches[2];
        } elseif (preg_match('/^(vi|en)$/', $currentPath)) {
            $cleanPath = '';
        }

        // Create new URL with locale
        if (empty($cleanPath)) {
            return "/$locale";
        }

        return "/$locale/$cleanPath";
    }

    public function render()
    {
        return view('livewire.header-component');
    }
}