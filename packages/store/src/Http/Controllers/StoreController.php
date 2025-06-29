<?php

namespace Packages\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Store\Services\StoreService;
use Packages\Tenant\Services\TenantService;
use Packages\Log\Traits\Loggable;

class StoreController extends Controller
{
    use Loggable;

    protected StoreService $storeService;
    protected TenantService $tenantService;

    public function __construct(StoreService $storeService, TenantService $tenantService)
    {
        $this->storeService = $storeService;
        $this->tenantService = $tenantService;
    }

    /**
     * Display store selection page.
     */
    public function selection()
    {
        $this->logActivity('store_selection_page_accessed', [
            'user_id' => Auth::id(),
            'current_store_id' => $this->tenantService->getCurrentStoreId(),
        ]);

        return view('store::pages.store-selection');
    }

    /**
     * Switch to a different store.
     */
    public function switch(Request $request)
    {
        // Handle both GET and POST requests
        $storeId = $request->input('store_id') ?? $request->query('store_id');

        if (!$storeId) {
            session()->flash('error', 'Store ID is required');
            return back();
        }

        $request->merge(['store_id' => $storeId]);
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id'
        ]);

        try {
            $storeId = $request->input('store_id');

            $this->logActivity('store_switch_request', [
                'user_id' => Auth::id(),
                'current_store_id' => $this->tenantService->getCurrentStoreId(),
                'target_store_id' => $storeId,
            ]);

            $store = $this->tenantService->switchStore($storeId);

            $this->logActivity('store_switched_via_controller', [
                'user_id' => Auth::id(),
                'new_store_id' => $store->id,
                'store_name' => $store->name,
            ]);

            // Show success message
            session()->flash('success', "Switched to {$store->name}");

            // Redirect to dashboard
            return redirect()->route('locale.dashboard', ['locale' => app()->getLocale()]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'switch_store_via_controller',
                'user_id' => Auth::id(),
                'target_store_id' => $request->input('store_id'),
            ]);

            session()->flash('error', 'Failed to switch store: ' . $e->getMessage());

            return back();
        }
    }

    /**
     * Display a listing of stores.
     */
    public function index(Request $request)
    {
        $this->tenantService->requirePermission('manage_settings');

        $criteria = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'user_id' => auth()->id(),
        ];

        $stores = $this->storeService->searchStores($criteria, 15);

        $this->logActivity('stores_index_viewed', [
            'user_id' => auth()->id(),
            'search_criteria' => $criteria,
            'results_count' => $stores->count(),
        ]);

        return view('store::pages.stores.index', compact('stores', 'criteria'));
    }

    /**
     * Show the form for creating a new store.
     */
    public function create()
    {
        $this->tenantService->requirePermission('manage_settings');

        $this->logActivity('store_create_form_viewed', [
            'user_id' => auth()->id(),
        ]);

        return view('store::pages.stores.create');
    }

    /**
     * Store a newly created store.
     */
    public function store(Request $request)
    {
        $this->tenantService->requirePermission('manage_settings');

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores,slug',
            'domain' => 'nullable|string|max:255|unique:stores,domain',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
        ]);

        try {
            $store = $this->storeService->createStore($request->all());

            return redirect()->route('stores.show', $store)
                ->with('success', 'Store created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create store: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified store.
     */
    public function show(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $store = $this->storeService->getStoreById($storeId);

            $this->logActivity('store_details_viewed', [
                'user_id' => auth()->id(),
                'store_id' => $store->id,
            ]);

            return view('store::pages.stores.show', compact('store'));

        } catch (\Exception $e) {
            return redirect()->route('stores.index')
                ->with('error', 'Store not found.');
        }
    }

    /**
     * Show the form for editing the specified store.
     */
    public function edit(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $store = $this->storeService->getStoreById($storeId);

            $this->logActivity('store_edit_form_viewed', [
                'user_id' => auth()->id(),
                'store_id' => $store->id,
            ]);

            return view('store::pages.stores.edit', compact('store'));

        } catch (\Exception $e) {
            return redirect()->route('stores.index')
                ->with('error', 'Store not found.');
        }
    }

    /**
     * Update the specified store.
     */
    public function update(Request $request, int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stores,slug,' . $storeId,
            'domain' => 'nullable|string|max:255|unique:stores,domain,' . $storeId,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|size:3',
            'language' => 'required|string|size:2',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            $store = $this->storeService->updateStore($storeId, $request->all());

            return redirect()->route('stores.show', $store)
                ->with('success', 'Store updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update store: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified store.
     */
    public function destroy(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $this->storeService->deleteStore($storeId);

            return redirect()->route('stores.index')
                ->with('success', 'Store deleted successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete store: ' . $e->getMessage());
        }
    }

    /**
     * Activate store.
     */
    public function activate(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $this->storeService->activateStore($storeId);

            return back()->with('success', 'Store activated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to activate store: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate store.
     */
    public function deactivate(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $this->storeService->deactivateStore($storeId);

            return back()->with('success', 'Store deactivated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to deactivate store: ' . $e->getMessage());
        }
    }

    /**
     * Suspend store.
     */
    public function suspend(int $storeId)
    {
        $this->tenantService->requirePermission('manage_settings');

        try {
            $this->storeService->suspendStore($storeId);

            return back()->with('success', 'Store suspended successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to suspend store: ' . $e->getMessage());
        }
    }
}
