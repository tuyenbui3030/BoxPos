<?php

namespace Packages\Common\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Common\Services\StoreService;
use Packages\Common\Traits\Loggable;
use Symfony\Component\HttpFoundation\Response;

class StoreContextMiddleware
{
    use Loggable;

    protected StoreService $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Log middleware execution
        $this->logActivity('store_context_middleware_started', [
            'user_id' => $user->id,
            'current_store_id' => $user->current_store_id,
            'request_path' => $request->path(),
        ]);

        // Check if user has a current store set
        if (!$user->current_store_id) {
            $this->logSecurityEvent('user_without_store_context', [
                'user_id' => $user->id,
                'request_path' => $request->path(),
            ]);

            // Redirect to store selection if no current store
            return redirect()->route('stores.select')
                ->with('error', 'Please select a store to continue.');
        }

        // Verify user has access to the current store
        if (!$this->storeService->hasStoreAccess($user, $user->current_store_id)) {
            $this->logSecurityEvent('unauthorized_store_access_attempt', [
                'user_id' => $user->id,
                'attempted_store_id' => $user->current_store_id,
                'request_path' => $request->path(),
            ]);

            // Clear invalid store and redirect to selection
            $user->update(['current_store_id' => null]);
            
            return redirect()->route('stores.select')
                ->with('error', 'You do not have access to the selected store.');
        }

        // Set store context in the service
        $this->storeService->setCurrentStore($user->current_store_id);

        // Add store context to request for easy access
        $request->merge([
            'current_store_id' => $user->current_store_id,
            'current_store' => $this->storeService->getCurrentStore(),
        ]);

        $this->logActivity('store_context_middleware_completed', [
            'user_id' => $user->id,
            'store_id' => $user->current_store_id,
            'request_path' => $request->path(),
        ]);

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Log request completion with store context
        if (Auth::check()) {
            $this->logActivity('request_completed_with_store_context', [
                'user_id' => Auth::id(),
                'store_id' => Auth::user()->current_store_id,
                'request_path' => $request->path(),
                'response_status' => $response->getStatusCode(),
                'memory_usage' => memory_get_peak_usage(true),
            ]);
        }
    }
}