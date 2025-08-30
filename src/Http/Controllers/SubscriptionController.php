<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Http\Request;
use Juzaweb\Core\Http\Controllers\ThemeController;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;

class SubscriptionController extends ThemeController
{
    public function subscribe(Request $request, string $module)
    {
        $method = SubscriptionMethod::withTranslation()->findOrFail($request->input('method_id'));
        $plan = Plan::withTranslation()->find($request->input('plan_id'));
        $subscription = Subscription::driver($method->driver)
            ->setConfigs($method->config)
            ->sandbox();
        $user = $request->user();

        try {
            $subscription->subscribe($plan,
                [
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                ]
            );
        } catch (SubscriptionException $e) {
            return $this->error(['message' => $e->getMessage()]);
        }

        return $this->success(['message' => 'Subscription created successfully']);
    }
    
    public function return(Request $request, string $module)
    {
        dd($request->all());
    }

    public function cancel(Request $request, string $module)
    {
        dd($request->all());
    }
}
