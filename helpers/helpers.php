<?php

use Juzaweb\CMS\Models\User;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\UserSubscription;

if (!function_exists('has_subscription')) {
    function has_subscription(User $user): ?UserSubscription
    {
        return UserSubscription::with(['plan' => fn($q) => $q->cacheFor(3600)])
            ->whereUserId($user->id)
            ->cacheFor(3600)
            ->inEffect()
            ->first();
    }
}

if (!function_exists('subscripted_plan')) {
    function subscripted_plan(User $user): ?Plan
    {
        return has_subscription($user)?->plan;
    }
}
