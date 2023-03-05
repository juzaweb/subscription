<?php

namespace Juzaweb\Subscription\Http\Controllers\Frontend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Juzaweb\CMS\Http\Controllers\FrontendController;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Exceptions\SubscriptionExistException;
use Juzaweb\Subscription\Http\Requests\Frontend\PaymentRequest;
use Juzaweb\Subscription\Models\PaymentHistory;
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

    public function payment(PaymentRequest $request, $module, $plan, $method)
    {
        $plan = $this->planRepository->findByUUID($plan);
        $planMethod = $plan->planPaymentMethods()->where(['method' => $method])->first();

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);

        if (empty($planMethod)) {
            $planMethod = $this->subscription->createPlanMethod($plan, $method);
        }

        $helper = $this->paymentMethodManager->find($method);
        if ($helper->isRedirect()) {
            return redirect()->to($helper->getRedirectUrl($planMethod));
        }
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
            return $this->success(
                [
                    'message' => trans('subscription::content.payment_success'),
                    'redirect' => $this->getReturnPageUrl(),
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success(
            [
                'message' => trans('subscription::content.payment_success'),
                'redirect' => $this->getReturnPageUrl(),
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success(
            [
                'redirect' => $this->getCancelPageUrl(),
            ]
        );
    }

    public function webhook(Request $request, $module, $method): \Illuminate\Http\Response
    {
        Log::info("Subscription Webhook {$module} {$method}", $request->all());

        $method = $this->paymentMethodRepository->findByMethod($method, $module, true);

        DB::beginTransaction();
        try {
            $helper = $this->paymentMethodManager->find($method);

            $agreement = $helper->webhook($request);

            if (empty($agreement)) {
                throw new PaymentException('Webhook: Not available agreement ' . json_encode($request->all()));
            }

            $subscriber = UserSubscription::where(
                [
                    'agreement_id' => $agreement->getAgreementId(),
                    'module' => $module,
                ]
            )
                ->first();

            if (empty($subscriber->user)) {
                throw new PaymentException('Webhook: Empty user ' . json_encode($request->all()));
            }

            if ($subscriber->end_date?->gt(now())) {
                $expirationDate = $subscriber->end_date->addMonth()->format('Y-m-d 23:59:59');
            } else {
                $expirationDate = now()->addMonth()->format('Y-m-d 23:59:59');
            }

            $subscriber->update(['start_date' => $subscriber->start_date ?? now(), 'end_date' => $expirationDate]);

            PaymentHistory::create(
                [
                    'token' => $agreement->getToken(),
                    'method' => $method->method,
                    'module' => $module,
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

            DB::commit();
        } catch (PaymentException $e) {
            DB::rollBack();
            report($e);
            return response($e->getMessage(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response('Webhook Handled', 200);
    }

    protected function getReturnPageUrl(): string
    {
        return '/';
    }

    protected function getCancelPageUrl(): string
    {
        return '/';
    }
}
