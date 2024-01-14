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
use Juzaweb\Subscription\Contrasts\PaymentResult;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Events\PaymentReturn;
use Juzaweb\Subscription\Events\PaymentSuccess;
use Juzaweb\Subscription\Events\WebhookHandleSuccess;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Exceptions\SubscriptionExistException;
use Juzaweb\Subscription\Exceptions\WebhookPaymentSkipException;
use Juzaweb\Subscription\Http\Requests\Frontend\PaymentRequest;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
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
        Cache::set("subscription_payment_id_{$jw_user->id}", $request->input('id'), 3600);

        $method = $request->post('method');
        $plan = $this->planRepository->findByUUIDOrFail($request->post('plan'));

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);
        $moduleRegistion = $this->subscription->getModule($module);

        try {
            $result = DB::transaction(
                function () use ($method, $plan, $request) {
                    $planMethod = $this->getPlanMethod($plan, $method);

                    return $this->paymentMethodManager->find($method)->subscribe($plan, $planMethod, $request);
                }
            );

            if ($handler = $moduleRegistion->get('handler')) {
                $handler = app()->make($handler);

                $handler->onPayment($result->withData($request->all()));
            }
        } catch (PaymentException $e) {
            return $this->error($e->getMessage());
        }

        if ($result->isRedirect()) {
            return $this->success(
                [
                    'redirect' => $result->getRedirectUrl(),
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

    public function return(
        Request $request,
        string $module,
        string $plan,
        string $method
    ): JsonResponse|RedirectResponse {
        global $jw_user;

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);
        $plan = $this->planRepository->findByUUID($plan, true);
        $moduleRegistion = $this->subscription->getModule($module);
        $paymentId = Cache::get("subscription_payment_id_{$jw_user->id}");

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);
            $result = $helper->return($plan, $request->all());

            if (PaymentHistory::where(['token' => $result->getToken()])->exists()) {
                throw new SubscriptionExistException('Payment already exist.');
            }

            $paymentHistory = PaymentHistory::create(
                [
                    'token' => $result->getToken(),
                    'method' => $method->method,
                    'module' => $module,
                    'type' => PaymentHistory::TYPE_RETURN,
                    'amount' => $result->getAmount(),
                    'method_id' => $method->id,
                    'plan_id' => $plan->id,
                    'user_id' => Auth::id(),
                    'agreement_id' => $result->getAgreementId(),
                    'status' => PaymentHistory::STATUS_REGISTER,
                ]
            );

            $subscriber = ModuleSubscription::updateOrCreate(
                [
                    'module_id' => $paymentId,
                    'module_type' => $module,
                ],
                [
                    'plan_id' => $result->plan->id,
                    'method_id' => $result->method->id,
                    'agreement_id' => $result->getAgreementId(),
                    'amount' => $result->getAmount(),
                    'register_by' => Auth::id(),
                    'status' => ModuleSubscription::STATUS_PENDING,
                ]
            );

            if ($result->canActiveSubscription()) {
                $subscriber->activeSubscription();
            }

            // handler
            if ($handler = $moduleRegistion->get('handler')) {
                app()->make($handler)->onReturn(
                    $result->withPlan($plan)
                        ->withMethod($method)
                        ->withPaymentHistory($paymentHistory)
                );
            }

            DB::commit();
        } catch (PaymentException|SubscriptionExistException $e) {
            report($e);
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

        event(new PaymentReturn($result, $paymentHistory));

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

    public function webhook(Request $request, string $module, string $method): Response
    {
        Log::info(
            "Subscription Webhook {$module} {$method}"
            ."\n Request: " .json_encode($request->all(), JSON_THROW_ON_ERROR)
            ."\n Headers: ".json_encode($request->headers->all(), JSON_THROW_ON_ERROR)
        );

        $method = $this->paymentMethodRepository->findByMethod($method, $module);

        if ($method === null) {
            Log::info('Webhook: Method not found', ['method' => $method, 'module' => $module]);
            return response('Webhook Handle Failed', 200);
        }

        $moduleRegistion = $this->subscription->getModule($module);

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);

            $agreement = $helper->webhook($request);

            throw_unless($agreement, new WebhookPaymentSkipException('Webhook: There is no handling.'));

            $paymentHistory = $this->webhookHandle($agreement, $method);

            throw_if($paymentHistory === null, new WebhookPaymentSkipException('Webhook: Payment not found.'));

            if ($handler = $moduleRegistion->get('handler')) {
                app()->make($handler)->onWebhook(
                    $agreement->withPlan($paymentHistory->plan)
                        ->withMethod($method)
                        ->withPaymentHistory($paymentHistory)
                );
            }

            event(new WebhookHandleSuccess($agreement, $method, $paymentHistory));

            DB::commit();
        } catch (PaymentException|WebhookPaymentSkipException $e) {
            DB::rollBack();
            report($e);
            return response($e->getMessage(), 200);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return response('Webhook Handle Failed', 422);
        }

        if ($agreement->isActive()) {
            event(new PaymentSuccess($agreement, $paymentHistory));
        }

        return response('Webhook Handled', 200);
    }

    protected function webhookHandle(
        PaymentResult $agreement,
        PaymentMethod $method
    ): ?PaymentHistory {
        $historyExists = PaymentHistory::where(
            [
                'method' => $method->method,
                'module' => $method->module,
                'type' => PaymentHistory::TYPE_WEBHOOK,
                'agreement_id' => $agreement->getAgreementId(),
                'token' => $agreement->getToken(),
            ]
        )->exists();

        throw_if($historyExists, new WebhookPaymentSkipException('Webhook: Already handled.'));

        $returnPaymentHistory = PaymentHistory::where(
            [
                'method' => $method->method,
                'module' => $method->module,
                'type' => PaymentHistory::TYPE_RETURN,
                'agreement_id' => $agreement->getAgreementId(),
            ]
        )->first();

        throw_if(
            $returnPaymentHistory === null,
            new WebhookPaymentSkipException('Cannot find return payment history.')
        );

        return PaymentHistory::create(
            [
                'token' => $agreement->getToken(),
                'method' => $method->method,
                'module' => $method->module,
                'type' => PaymentHistory::TYPE_WEBHOOK,
                'amount' => $agreement->getAmount(),
                'method_id' => $method->id,
                'plan_id' => $returnPaymentHistory->plan_id,
                'user_id' => $returnPaymentHistory->user_id,
                'agreement_id' => $agreement->getAgreementId(),
                'status' => $agreement->getStatus(),
            ]
        );
    }

    protected function getPlanMethod(Plan $plan, PaymentMethod $method): PlanPaymentMethod
    {
        /** @var null|PlanPaymentMethod $planMethod */
        $planMethod = $plan->planPaymentMethods()->where(['method' => $method->method])->first();

        return $planMethod ?? $this->subscription->createPlanMethod($plan, $method);
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
