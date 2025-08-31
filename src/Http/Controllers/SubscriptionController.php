<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Juzaweb\Core\Http\Controllers\ThemeController;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;

class SubscriptionController extends ThemeController
{
    public function subscribe(Request $request, string $module)
    {
        /** @var SubscriptionMethod $method */
        $method = SubscriptionMethod::withTranslation()->findOrFail($request->input('method_id'));
        $plan = Plan::withTranslation()->find($request->input('plan_id'));
        $user = $request->user();

        try {
            $payment = DB::transaction(fn() => Subscription::create($user, $module, $plan, $method, $request->all(), $module == 'test'));
        } catch (SubscriptionException $e) {
            return $this->error(['message' => $e->getMessage()]);
        }

        if ($payment->isRedirect()) {
            if ($method->paymentDriver()->isReturnInEmbed()) {
                return $this->success(
                    [
                        'type' => 'embed',
                        'embed_url' => $payment->getRedirectUrl(),
                        'payment_history_id' => $payment->getPaymentHistory()->id,
                    ]
                );
            }

            return $this->success(
                [
                    'type' => 'redirect',
                    'redirect' => $payment->getRedirectUrl(),
                    'message' => __('Redirecting to payment gateway...'),
                ]
            );
        }

        return $this->success(['message' => __('Subscription created successfully')]);
    }
    
    public function return(Request $request, string $module, string $subscriptionHistoryId)
    {
        dd($request->all());
    }

    public function cancel(Request $request, string $module, string $subscriptionHistoryId)
    {
        dd($request->all());
    }
}
