<?php

use Illuminate\Database\Eloquent\Model;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Contrasts\WithSubscriptable;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\Plan;

if (!function_exists('has_subscription')) {
    function has_subscription(Model $subsctiption, string $module): ?ModuleSubscription
    {
        return ModuleSubscription::with(['plan' => fn ($q) => $q->cacheFor(3600)])
            ->where(['module_id' => $subsctiption->id, 'module_type' => $module])
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

if (!function_exists('get_subscripted_plan')) {
    function get_subscripted_plan(int|string $planId): ?Plan
    {
        if (is_numeric($planId)) {
            return Plan::where('id', $planId)->first();
        }

        return Plan::where('uuid', $planId)->first();
    }
}

if (!function_exists('get_subscription_by_id')) {
    function get_subscription_by_id(int|string $subscriptionId, string $module): ?WithSubscriptable
    {
        $model = app()->make(Subscription::class)->getModule($module)->get('model');

        if (is_numeric($subscriptionId)) {
            return app($model)->where('id', $subscriptionId)->first();
        }

        return app($model)->where('uuid', $subscriptionId)->first();
    }
}
