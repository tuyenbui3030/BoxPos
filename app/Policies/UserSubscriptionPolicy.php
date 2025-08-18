<?php

namespace App\Policies;

use App\Models\UserSubscription;
use Packages\User\Models\User;

class UserSubscriptionPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserSubscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserSubscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserSubscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }
}