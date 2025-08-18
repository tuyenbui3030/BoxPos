<?php

namespace Packages\Marketplace\Livewire;

use Livewire\Component;
use App\Models\Application;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;
use Packages\Log\Traits\Loggable;

class AppSwitcher extends Component
{
    use Loggable; // ← MANDATORY

    public $currentApp = null;
    public $availableApps = [];
    public $userSubscriptions = [];

    public function mount()
    {
        $this->loadUserApps();
    }

    /**
     * Load user's apps and subscriptions
     */
    public function loadUserApps()
    {
        if (!auth()->check()) {
            return;
        }

        $this->logActivity('app_switcher_loaded', [
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();
        
        // Get user's active subscriptions
        $this->userSubscriptions = UserSubscription::with('application')
            ->where('user_id', $user->id)
            ->active()
            ->get()
            ->keyBy('application.slug');

        // Get current app from session or current store
        $currentStore = $user->currentStore;
        if ($currentStore && $currentStore->application) {
            $this->currentApp = $currentStore->application;
        } else {
            // Default to first subscribed app
            $firstSubscription = $this->userSubscriptions->first();
            $this->currentApp = $firstSubscription?->application;
        }

        // Get all available apps for marketplace
        $this->availableApps = Application::active()
            ->ordered()
            ->get();
    }

    /**
     * Switch to different application
     */
    public function switchToApp($appSlug)
    {
        if (!auth()->check()) {
            return;
        }

        $this->logActivity('app_switch_started', [
            'user_id' => auth()->id(),
            'target_app' => $appSlug,
            'current_app' => $this->currentApp?->slug,
        ]);

        $user = auth()->user();
        $subscription = $this->userSubscriptions->get($appSlug);

        if (!$subscription) {
            // Redirect to marketplace to subscribe
            $this->logActivity('app_switch_redirect_to_marketplace', [
                'user_id' => auth()->id(),
                'target_app' => $appSlug,
                'reason' => 'no_subscription',
            ]);
            
            return redirect()->route('marketplace.subscribe', $appSlug);
        }

        try {
            // Find a store for this app or create one if needed
            $store = $subscription->stores()->first();
            
            if (!$store) {
                // Create a default store for this app
                $application = $subscription->application;
                $store = \Packages\Store\Models\Store::create([
                    'name' => $application->name . ' Store',
                    'slug' => \Str::slug($application->name . '-' . $user->id),
                    'application_id' => $application->id,
                    'subscription_id' => $subscription->id,
                    'status' => 'active',
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'currency' => 'VND',
                    'language' => 'vi',
                    'app_settings' => $this->getDefaultAppSettings($application),
                ]);

                // Create user-store relationship
                \Packages\Store\Models\UserStore::create([
                    'user_id' => $user->id,
                    'store_id' => $store->id,
                    'role' => 'admin',
                    'is_active' => true,
                    'joined_at' => now(),
                    'permissions' => ['*'],
                ]);

                $this->logActivity('app_switch_store_created', [
                    'user_id' => auth()->id(),
                    'app_slug' => $appSlug,
                    'store_id' => $store->id,
                ]);
            }

            // Switch to this store
            $user->setCurrentStore($store->id);
            
            // Set app context in session
            session(['current_app' => $appSlug]);
            
            $this->logActivity('app_switch_completed', [
                'user_id' => auth()->id(),
                'app_slug' => $appSlug,
                'store_id' => $store->id,
            ]);

            // Dispatch success event
            $this->dispatch('app-switched', [
                'app' => $appSlug,
                'store' => $store->id,
                'message' => "Switched to {$subscription->application->name}"
            ]);

            // Show success message and redirect
            session()->flash('success', "Switched to {$subscription->application->name}!");
            
            // Refresh the page to load new app packages
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            $this->logError($e, [
                'component' => 'AppSwitcher',
                'action' => 'switchToApp',
                'user_id' => auth()->id(),
                'target_app' => $appSlug,
            ]);

            // Dispatch error event
            $this->dispatch('app-switch-error', [
                'message' => $e->getMessage()
            ]);

            session()->flash('error', 'Failed to switch app: ' . $e->getMessage());
        }
    }

    /**
     * Get default app settings based on application type
     */
    public function getDefaultAppSettings($application)
    {
        return match($application->slug) {
            'building-pos' => [
                'inventory_tracking' => true,
                'auto_reorder' => false,
                'price_alerts' => true,
                'supplier_integration' => true,
            ],
            'clinic-pos' => [
                'appointment_reminders' => true,
                'patient_privacy' => true,
                'insurance_integration' => false,
                'telemedicine' => false,
            ],
            'restaurant-pos' => [
                'table_management' => true,
                'kitchen_display' => true,
                'delivery_integration' => false,
                'loyalty_program' => true,
            ],
            'beauty-pos' => [
                'appointment_booking' => true,
                'service_packages' => true,
                'commission_tracking' => true,
                'online_booking' => false,
            ],
            default => [],
        };
    }

    public function render()
    {
        return view('marketplace::livewire.app-switcher');
    }
}