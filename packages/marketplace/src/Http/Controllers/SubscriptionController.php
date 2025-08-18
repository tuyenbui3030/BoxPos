<?php

namespace Packages\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSubscription;
use Packages\Marketplace\Services\SubscriptionService;
use Packages\Marketplace\Http\Requests\UpgradeSubscriptionRequest;
use Packages\Log\Traits\Loggable;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    use Loggable; // ← MANDATORY

    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        
        // Apply logging middleware
        $this->middleware('log.requests')->only(['upgrade', 'cancel', 'reactivate']);
    }

    /**
     * Display user's subscriptions
     */
    public function index()
    {
        $this->logActivity('subscriptions_viewed', [
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();
        
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with(['application', 'stores'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('marketplace::subscriptions.index', compact('subscriptions'));
    }
    
    /**
     * Show specific subscription details
     */
    public function show(UserSubscription $subscription)
    {
        $this->authorize('view', $subscription);
        
        $this->logActivity('subscription_details_viewed', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
        ]);
        
        $subscription->load(['application', 'stores']);
        
        return view('marketplace::subscriptions.show', compact('subscription'));
    }
    
    /**
     * Upgrade subscription to new plan
     */
    public function upgrade(UpgradeSubscriptionRequest $request, UserSubscription $subscription)
    {
        $this->authorize('update', $subscription);
        
        $this->logActivity('subscription_upgrade_started', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
            'current_plan' => $subscription->plan_type,
            'new_plan' => $request->plan_type,
        ]);
        
        try {
            $this->subscriptionService->upgradeSubscription($subscription, $request->plan_type);
            
            return $request->expectsJson()
                ? response()->json([
                    'success' => true,
                    'message' => 'Subscription upgraded successfully!'
                ])
                : redirect()->back()->with('success', 'Subscription upgraded successfully!');
                
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'SubscriptionController',
                'action' => 'upgrade',
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
            ]);
            
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400)
                : redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Cancel subscription
     */
    public function cancel(UserSubscription $subscription)
    {
        $this->authorize('update', $subscription);
        
        $this->logActivity('subscription_cancellation_requested', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
        ]);
        
        try {
            $this->subscriptionService->cancelSubscription($subscription);
            
            return redirect()->back()->with('success', 'Subscription cancelled successfully.');
            
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'SubscriptionController',
                'action' => 'cancel',
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
            ]);
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Reactivate cancelled subscription
     */
    public function reactivate(UserSubscription $subscription)
    {
        $this->authorize('update', $subscription);
        
        $this->logActivity('subscription_reactivation_requested', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
        ]);
        
        try {
            $this->subscriptionService->reactivateSubscription($subscription);
            
            return redirect()->back()->with('success', 'Subscription reactivated successfully.');
            
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'SubscriptionController',
                'action' => 'reactivate',
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
            ]);
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Display billing information
     */
    public function billing()
    {
        $this->logActivity('billing_viewed', [
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();
        
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with('application')
            ->get();
        
        return view('marketplace::billing.index', compact('subscriptions'));
    }
}