<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Juzaweb\Core\Http\Controllers\ThemeController;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
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
            $payment = DB::transaction(
                fn() => Subscription::create($user, $module, $plan, $method, $request->all())
            );
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
        $returnUrl = Subscription::module($module)->getReturnUrl();

        try {
            $payment = DB::transaction(
                function () use ($request, $module, $subscriptionHistoryId) {
                    $history = SubscriptionHistory::lockForUpdate()->find($subscriptionHistoryId);

                    throw_if($history === null, SubscriptionException::class, __('Subscription not found'));

                    return Subscription::complete($history, $request->all());
                }
            );
        } catch (SubscriptionException $e) {
            return $this->error(['message' => $e->getMessage(), 'redirect' => $returnUrl]);
        }

        if ($payment->isSuccessful()) {
            return $this->success(
                [
                    'message' => __('Payment completed successfully!'),
                    'redirect' => $returnUrl,
                ]
            );
        }

        return $this->error(
            [
                'message' => __('Payment failed!'),
                'redirect' => $returnUrl,
            ]
        );
    }

    public function cancel(Request $request, string $module, string $subscriptionHistoryId)
    {
        $returnUrl = Subscription::module($module)->getReturnUrl();

        try {
            $payment = DB::transaction(
                function () use ($request, $module, $subscriptionHistoryId) {
                    $history = SubscriptionHistory::lockForUpdate()->find($subscriptionHistoryId);

                    throw_if($history === null, SubscriptionException::class, __('Subscription not found'));

                    return Subscription::cancel($history, $request->all());
                }
            );
        } catch (SubscriptionException $e) {
            return $this->error([
                'message' => $e->getMessage(),
                'redirect' => $returnUrl,
            ]);
        }

        return $this->success([
            'message' => __('Subscription cancelled successfully'),
            'redirect' => $returnUrl,
        ]);
    }

    public function webhook(Request $request, string $module, string $driver)
    {
        $result = Subscription::webhook($request, $module, $driver);

        if ($result->isSuccessful()) {
            return response('Webhook Handled', 200);
        }

        return response('Webhook Failed', 200);
    }
}
