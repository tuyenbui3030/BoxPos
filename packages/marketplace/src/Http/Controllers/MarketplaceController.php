<?php

namespace Packages\Marketplace\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\UserSubscription;
use Packages\Marketplace\Services\MarketplaceService;
use Packages\Marketplace\Http\Requests\SubscribeToAppRequest;
use Packages\Log\Traits\Loggable;
use App\Http\Controllers\Controller;

class MarketplaceController extends Controller
{
    use Loggable; // ← MANDATORY

    protected MarketplaceService $marketplaceService;

    public function __construct(MarketplaceService $marketplaceService)
    {
        $this->marketplaceService = $marketplaceService;
        
        // Apply logging middleware
        $this->middleware('log.requests')->only(['subscribe', 'startTrial']);
    }

    /**
     * Display marketplace index page
     */
    public function index()
    {
        $this->logActivity('marketplace_viewed', [
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();
        
        // Get all available applications
        $applications = Application::ordered()->get();
        
        // Get user's current subscriptions
        $userSubscriptions = UserSubscription::where('user_id', $user->id)
            ->with('application')
            ->get()
            ->keyBy('application.slug');
        
        return view('marketplace::index', compact('applications', 'userSubscriptions'));
    }
    
    /**
     * Show specific application details
     */
    public function show(Application $app)
    {
        $this->logActivity('app_details_viewed', [
            'user_id' => auth()->id(),
            'app_slug' => $app->slug,
        ]);

        $user = auth()->user();
        
        // Check if user already has subscription
        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('application_id', $app->id)
            ->first();
        
        return view('marketplace::show', compact('app', 'subscription'));
    }
    
    /**
     * Subscribe user to application
     */
    public function subscribe(SubscribeToAppRequest $request, Application $app)
    {
        $this->logActivity('app_subscription_started', [
            'user_id' => auth()->id(),
            'app_slug' => $app->slug,
            'plan_type' => $request->plan_type,
            'ip_address' => $request->ip(),
        ]);
        
        $user = auth()->user();
        $planType = $request->plan_type;
        
        try {
            $subscription = $this->marketplaceService->subscribeToApp($user->id, $app, $planType);
            
            $this->logActivity('app_subscription_completed', [
                'user_id' => $user->id,
                'app_slug' => $app->slug,
                'subscription_id' => $subscription->id,
                'plan_type' => $planType,
            ]);
            
            return $request->expectsJson()
                ? response()->json([
                    'success' => true,
                    'message' => "Successfully subscribed to {$app->name}!",
                    'subscription' => $subscription
                ])
                : redirect()->route('marketplace.index')
                    ->with('success', "Successfully subscribed to {$app->name}!");
                
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'MarketplaceController',
                'action' => 'subscribe',
                'user_id' => auth()->id(),
                'app_slug' => $app->slug,
                'plan_type' => $planType,
            ]);
            
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400)
                : redirect()->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Start free trial for application
     */
    public function startTrial(Application $app)
    {
        $this->logActivity('app_trial_started', [
            'user_id' => auth()->id(),
            'app_slug' => $app->slug,
        ]);
        
        $user = auth()->user();
        
        try {
            $subscription = $this->marketplaceService->startTrial($user->id, $app);
            
            $this->logActivity('app_trial_completed', [
                'user_id' => $user->id,
                'app_slug' => $app->slug,
                'subscription_id' => $subscription->id,
            ]);
            
            return redirect()->route('marketplace.index')
                ->with('success', "Started 14-day free trial for {$app->name}!");
                
        } catch (\Exception $e) {
            $this->logError($e, [
                'controller' => 'MarketplaceController',
                'action' => 'startTrial',
                'user_id' => auth()->id(),
                'app_slug' => $app->slug,
            ]);
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}