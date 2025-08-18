<?php

namespace Packages\Marketplace\Services;

use App\Models\UserSubscription;
use Packages\Log\Traits\Loggable;

class SubscriptionService
{
    use Loggable; // ← MANDATORY

    /**
     * Upgrade user subscription to new plan
     *
     * @param UserSubscription $subscription
     * @param string $newPlan
     * @return UserSubscription
     * @throws \Exception When upgrade fails
     */
    public function upgradeSubscription(UserSubscription $subscription, string $newPlan): UserSubscription
    {
        $this->logActivity('subscription_upgrade_service_started', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'current_plan' => $subscription->plan_type,
            'new_plan' => $newPlan,
        ]);

        $app = $subscription->application;
        $pricing = $app->getPricing($newPlan);
        
        if (!$pricing) {
            throw new \InvalidArgumentException('Invalid plan selected.');
        }

        try {
            $subscription->upgradeTo(
                $newPlan,
                $pricing['features'],
                $pricing['max_stores'],
                $pricing['monthly_price']
            );

            $this->logActivity('subscription_upgraded', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'old_plan' => $subscription->getOriginal('plan_type'),
                'new_plan' => $newPlan,
                'new_price' => $pricing['monthly_price'],
            ]);

            return $subscription;

        } catch (\Exception $e) {
            $this->logError($e, [
                'service' => 'SubscriptionService',
                'action' => 'upgradeSubscription',
                'subscription_id' => $subscription->id,
                'new_plan' => $newPlan,
            ]);

            throw $e;
        }
    }

    /**
     * Cancel user subscription
     *
     * @param UserSubscription $subscription
     * @return UserSubscription
     */
    public function cancelSubscription(UserSubscription $subscription): UserSubscription
    {
        $this->logActivity('subscription_cancellation_started', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'plan_type' => $subscription->plan_type,
        ]);

        try {
            $subscription->cancel();

            $this->logActivity('subscription_cancelled', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'cancelled_at' => now(),
            ]);

            return $subscription;

        } catch (\Exception $e) {
            $this->logError($e, [
                'service' => 'SubscriptionService',
                'action' => 'cancelSubscription',
                'subscription_id' => $subscription->id,
            ]);

            throw $e;
        }
    }

    /**
     * Reactivate cancelled subscription
     *
     * @param UserSubscription $subscription
     * @return UserSubscription
     */
    public function reactivateSubscription(UserSubscription $subscription): UserSubscription
    {
        $this->logActivity('subscription_reactivation_started', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
        ]);

        try {
            $subscription->reactivate();

            $this->logActivity('subscription_reactivated', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'reactivated_at' => now(),
            ]);

            return $subscription;

        } catch (\Exception $e) {
            $this->logError($e, [
                'service' => 'SubscriptionService',
                'action' => 'reactivateSubscription',
                'subscription_id' => $subscription->id,
            ]);

            throw $e;
        }
    }
}