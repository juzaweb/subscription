<?php

use Juzaweb\CMS\Models\Model;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\Plan;

if (!function_exists('has_subscription')) {
    function has_subscription(Model $subsctiption, string $module): ?ModuleSubscription
    {
        return ModuleSubscription::with(['plan' => fn ($q) => $q->cacheFor(3600)])
            ->where(['module_id' => $subsctiption->id, 'module_type' => $module])
            ->isActive()
            ->inEffect()
            ->cacheFor(3600)
            ->first();
    }
}

if (!function_exists('subscripted_plan')) {
    function subscripted_plan(Model $subsctiption, string $module): ?Plan
    {
        return has_subscription($subsctiption, $module)?->plan;
    }
}
