<?php

namespace Packages\Marketplace\Services;

use App\Models\Application;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Packages\Log\Traits\Loggable;

class MarketplaceService
{
    use Loggable; // ← MANDATORY

    /**
     * Subscribe user to an application with specified plan
     *
     * @param int $userId User ID
     * @param Application $app Application to subscribe to
     * @param string $planType Plan type (basic, professional, enterprise)
     * @return UserSubscription
     * @throws \Exception When subscription fails
     */
    public function subscribeToApp(int $userId, Application $app, string $planType): UserSubscription
    {
        $this->logActivity('app_subscription_service_started', [
            'user_id' => $userId,
            'app_id' => $app->id,
            'plan_type' => $planType,
        ]);

        // Get pricing for selected plan
        $pricing = $app->getPricing($planType);
        if (!$pricing) {
            throw new \InvalidArgumentException('Invalid plan selected.');
        }

        // Check if user already has subscription
        $existingSubscription = UserSubscription::where('user_id', $userId)
            ->where('application_id', $app->id)
            ->first();
            
        if ($existingSubscription) {
            throw new \Exception('User already has a subscription for this app.');
        }

        DB::beginTransaction();

        try {
            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $userId,
                'application_id' => $app->id,
                'plan_type' => $planType,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'max_stores' => $pricing['max_stores'],
                'features' => $pricing['features'],
                'monthly_price' => $pricing['monthly_price'],
                'billing_info' => [
                    'payment_method' => 'demo',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addMonth(),
            ]);

            // Create default store for this app
            $store = $this->createDefaultStore($app, $subscription, $userId);

            // Create user-store relationship
            $this->createUserStoreRelationship($userId, $store->id);

            DB::commit();

            $this->logActivity('app_subscription_service_completed', [
                'user_id' => $userId,
                'subscription_id' => $subscription->id,
                'store_id' => $store->id,
                'app_slug' => $app->slug,
            ]);

            return $subscription;

        } catch (\Exception $e) {
            DB::rollback();
            
            $this->logError($e, [
                'service' => 'MarketplaceService',
                'action' => 'subscribeToApp',
                'user_id' => $userId,
                'app_id' => $app->id,
                'plan_type' => $planType,
            ]);

            throw $e;
        }
    }

    /**
     * Start free trial for user
     *
     * @param int $userId User ID
     * @param Application $app Application to start trial for
     * @return UserSubscription
     * @throws \Exception When trial creation fails
     */
    public function startTrial(int $userId, Application $app): UserSubscription
    {
        $this->logActivity('app_trial_service_started', [
            'user_id' => $userId,
            'app_id' => $app->id,
        ]);

        // Check if user already has subscription
        $existingSubscription = UserSubscription::where('user_id', $userId)
            ->where('application_id', $app->id)
            ->first();
            
        if ($existingSubscription) {
            throw new \Exception('User already has a subscription for this app.');
        }

        DB::beginTransaction();

        try {
            // Create trial subscription
            $subscription = UserSubscription::create([
                'user_id' => $userId,
                'application_id' => $app->id,
                'plan_type' => 'trial',
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays(14),
                'max_stores' => 1,
                'features' => $app->getFeaturesForPlan('trial'),
                'monthly_price' => 0,
                'billing_info' => [
                    'payment_method' => 'trial',
                    'billing_cycle' => 'monthly',
                ],
                'next_billing_at' => now()->addDays(14),
            ]);

            // Create default store for this app
            $store = $this->createDefaultStore($app, $subscription, $userId, true);

            // Create user-store relationship
            $this->createUserStoreRelationship($userId, $store->id);

            DB::commit();

            $this->logActivity('app_trial_service_completed', [
                'user_id' => $userId,
                'subscription_id' => $subscription->id,
                'store_id' => $store->id,
                'app_slug' => $app->slug,
            ]);

            return $subscription;

        } catch (\Exception $e) {
            DB::rollback();
            
            $this->logError($e, [
                'service' => 'MarketplaceService',
                'action' => 'startTrial',
                'user_id' => $userId,
                'app_id' => $app->id,
            ]);

            throw $e;
        }
    }

    /**
     * Create default store for application
     */
    private function createDefaultStore(Application $app, UserSubscription $subscription, int $userId, bool $isTrial = false): \Packages\Store\Models\Store
    {
        $storeName = $app->name . ($isTrial ? ' Trial Store' : ' Store');
        $storeSlug = \Str::slug($app->name . ($isTrial ? '-trial' : '') . '-' . $userId . '-' . time());

        return \Packages\Store\Models\Store::create([
            'name' => $storeName,
            'slug' => $storeSlug,
            'application_id' => $app->id,
            'subscription_id' => $subscription->id,
            'status' => 'active',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
            'app_settings' => $this->getDefaultAppSettings($app),
            'is_demo' => $isTrial,
        ]);
    }

    /**
     * Create user-store relationship
     */
    private function createUserStoreRelationship(int $userId, int $storeId): void
    {
        \Packages\Store\Models\UserStore::create([
            'user_id' => $userId,
            'store_id' => $storeId,
            'role' => 'admin',
            'is_active' => true,
            'joined_at' => now(),
            'permissions' => ['*'],
        ]);
    }

    /**
     * Get default app settings based on application type
     */
    private function getDefaultAppSettings(Application $app): array
    {
        return match($app->slug) {
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
}