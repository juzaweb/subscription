<?php

namespace Juzaweb\Subscription\Http\Controllers\Frontend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Juzaweb\CMS\Http\Controllers\FrontendController;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Events\WebhookHandleSuccess;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Exceptions\SubscriptionExistException;
use Juzaweb\Subscription\Exceptions\WebhookPaymentSkipException;
use Juzaweb\Subscription\Http\Requests\Frontend\PaymentRequest;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\UserSubscription;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class PaymentController extends FrontendController
{
    public function __construct(
        protected PlanRepository $planRepository,
        protected Subscription $subscription,
        protected PaymentMethodRepository $paymentMethodRepository,
        protected PaymentMethodManager $paymentMethodManager
    ) {
    }

    public function payment(PaymentRequest $request, string $module): JsonResponse|RedirectResponse
    {
        global $jw_user;

        Cache::set("subscription_payment_{$jw_user->id}", $request->only(['return_url', 'cancel_url']), 3600);

        $plan = $this->planRepository->findByUUIDOrFail($request->post('plan'));
        $method = $request->post('method');

        $planMethod = $plan->planPaymentMethods()->where(['method' => $method])->first();

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);

        if (empty($planMethod)) {
            $planMethod = $this->subscription->createPlanMethod($plan, $method);
        }

        $helper = $this->paymentMethodManager->find($method);

        try {
            $helper->subscribe($plan, $planMethod, $request);
        } catch (PaymentException $e) {
            return $this->error($e->getMessage());
        }

        if ($helper->isRedirect()) {
            return $this->success(
                [
                    'redirect' => $helper->getRedirectUrl(),
                ]
            );
        }

        return $this->success(
            [
                'message' => trans('subscription::content.payment_success'),
                'redirect' => $this->getReturnPageUrl($module, $plan, $method),
            ]
        );
    }

    public function return(Request $request, $module, $plan, $method): JsonResponse|RedirectResponse
    {
        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);
        $plan = $this->planRepository->findByUUID($plan, true);

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);
            $result = $helper->return($plan, $request->all());

            if (PaymentHistory::where(['token' => $result->getToken()])->exists()) {
                throw new SubscriptionExistException('Payment already exist.');
            }

            $subscriber = UserSubscription::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'module' => $module,
                ],
                [
                    'plan_id' => $plan->id,
                    'method_id' => $method->id,
                    'agreement_id' => $result->getAgreementId(),
                    'amount' => $result->getAmount(),
                ]
            );

            PaymentHistory::create(
                [
                    'token' => $result->getToken(),
                    'method' => $method->method,
                    'module' => $module,
                    'type' => 'return',
                    'amount' => $result->getAmount(),
                    'method_id' => $method->id,
                    'plan_id' => $plan->id,
                    'user_subscription_id' => $subscriber->id,
                    'user_id' => Auth::id(),
                    'agreement_id' => $result->getAgreementId(),
                ]
            );

            DB::commit();
        } catch (PaymentException $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        } catch (SubscriptionExistException $e) {
            DB::rollBack();
            return $this->error(
                [
                    'message' => $e->getMessage(),
                    'redirect' => $this->getReturnPageUrl($module, $plan, $method),
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success(
            [
                'message' => trans('subscription::content.payment_success'),
                'redirect' => $this->getReturnPageUrl($module, $plan, $method),
            ]
        );
    }

    public function cancel(Request $request, $module, $plan, $method): JsonResponse|RedirectResponse
    {
        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);

            $helper->cancel();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success(
            [
                'redirect' => $this->getCancelPageUrl($module, $plan, $method),
            ]
        );
    }

    public function webhook(Request $request, $module, $method): Response
    {
        Log::info(
            "Subscription Webhook {$module} {$method}"
            ."\n Request: " .json_encode($request->all(), JSON_THROW_ON_ERROR)
            ."\n Headers: ".json_encode($request->headers->all(), JSON_THROW_ON_ERROR)
        );

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);

            $agreement = $helper->webhook($request);

            if (empty($agreement)) {
                throw new WebhookPaymentSkipException('Webhook: There is no handling.');
            }

            $subscriber = UserSubscription::where(
                [
                    'agreement_id' => $agreement->getAgreementId(),
                    'module' => $module,
                ]
            )
                ->first();

            if (empty($subscriber)) {
                throw new PaymentException('Webhook: Not available agreement.');
            }

            if (empty($subscriber->user)) {
                throw new PaymentException('Webhook: Subscriber model is empty user.');
            }

            $paymentHistory = $this->webhookHandle($agreement, $method, $subscriber);

            event(new WebhookHandleSuccess($agreement, $method, $subscriber, $paymentHistory));

            DB::commit();
        } catch (PaymentException $e) {
            DB::rollBack();
            report($e);
            return response($e->getMessage(), 422);
        } catch (WebhookPaymentSkipException $e) {
            DB::rollBack();
            report($e);
            return response($e->getMessage(), 200);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response('Webhook Handled', 200);
    }

    protected function webhookHandle(
        PaymentReturnResult $agreement,
        PaymentMethod $method,
        UserSubscription $subscriber
    ): ?PaymentHistory {
        if (!$agreement->isActive()) {
            $subscriber->update(['status' => $agreement->getStatus()]);
            return null;
        }

        if ($subscriber->end_date?->gt(now())) {
            $expirationDate = $subscriber->end_date->addMonth()->format('Y-m-d 23:59:59');
        } else {
            $expirationDate = now()->addMonth()->format('Y-m-d 23:59:59');
        }

        $historyExists = PaymentHistory::where(
            [
                'token' => $agreement->getToken(),
                'method' => $method->method,
                'module' => $method->module,
                'type' => 'webhook',
                'agreement_id' => $agreement->getAgreementId(),
            ]
        )->exists();

        throw_if($historyExists, new WebhookPaymentSkipException('Webhook: Already handled.'));

        $subscriber->update(['start_date' => $subscriber->start_date ?? now(), 'end_date' => $expirationDate]);

        return PaymentHistory::create(
            [
                'token' => $agreement->getToken(),
                'method' => $method->method,
                'module' => $method->module,
                'type' => 'webhook',
                'amount' => $agreement->getAmount(),
                'method_id' => $method->id,
                'plan_id' => $subscriber->plan_id,
                'user_subscription_id' => $subscriber->id,
                'user_id' => Auth::id(),
                'agreement_id' => $agreement->getAgreementId(),
                'end_date' => $expirationDate,
            ]
        );
    }

    protected function getReturnPageUrl($module, $plan, $method): string
    {
        global $jw_user;

        $url = Cache::get("subscription_payment_{$jw_user->id}")['return_url'] ?? '/';

        Cache::forget("subscription_payment_{$jw_user->id}");

        return apply_filters('subscription.return_page_url', $url, $module, $plan, $method);
    }

    protected function getCancelPageUrl($module, $plan, $method): string
    {
        global $jw_user;

        $url = Cache::get("subscription_payment_{$jw_user->id}")['cancel_url'] ?? '/';

        Cache::forget("subscription_payment_{$jw_user->id}");

        return apply_filters('subscription.cancel_page_url', $url, $module, $plan, $method);
    }
}
