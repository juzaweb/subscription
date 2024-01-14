<?php

use Juzaweb\CMS\Models\User;
use Juzaweb\Membership\Models\UserSubscription;
use Juzaweb\Subscription\Models\Plan;

if (!function_exists('has_subscription')) {
    function has_subscription(User $user, string $module): ?UserSubscription
    {
        return null;
        return UserSubscription::with(['plan' => fn($q) => $q->cacheFor(3600)])
            ->where(['module' => $module])
            ->whereUserId($user->id)
            ->cacheFor(3600)
            ->inEffect()
            ->first();
    }
}

if (!function_exists('subscripted_plan')) {
    function subscripted_plan(User $user, string $module): ?Plan
    {
        return has_subscription($user, $module)?->plan;
    }
}
