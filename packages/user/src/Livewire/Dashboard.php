<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\Appearance\Traits\HasAppearance;
use App\Traits\HandlesLocaleUpdates;
use Packages\Tenant\Services\TenantService;

class Dashboard extends Component
{
    use HasAppearance, HandlesLocaleUpdates;

    protected $listeners = [
        'locale-updated' => 'handleLocaleUpdate'
    ];

    public $layout = 'layouts.app';

    public function mount()
    {
        $tenantService = app(TenantService::class);
        $currentStore = $tenantService->getCurrentStore();

        if (!$currentStore) {
            return redirect()->route('store-selection');
        }

        // Redirect to specific dashboard based on store type
        if ($currentStore->slug === 'boxpos-dashboard') {
            return redirect()->route('locale.dashboard.business', ['locale' => app()->getLocale()]);
        } elseif ($currentStore->slug === 'coffee-bean-inventory') {
            return redirect()->route('locale.dashboard.coffee', ['locale' => app()->getLocale()]);
        }
    }

    #[Title('Dashboard')]
    public function render()
    {
        $tenantService = app(TenantService::class);
        $currentStore = $tenantService->getCurrentStore();

        return view('user::livewire.dashboard', compact('currentStore'));
    }
}
