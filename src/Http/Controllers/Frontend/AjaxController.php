<?php

namespace Juzaweb\Subscription\Http\Controllers\Frontend;

use Juzaweb\CMS\Http\Controllers\FrontendController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Http\Requests\Frontend\PaymentRequest;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class AjaxController extends FrontendController
{
    public function __construct(
        protected PlanRepository $planRepository,
        protected Subscription $subscription,
        protected PaymentMethodRepository $paymentMethodRepository
    ) {
    }

    public function subscriptionPayment(PaymentRequest $request)
    {
        $method = $request->input('method');
        $module = $request->input('module');

        $plan = $this->planRepository->find($request->input('plan_id'));
        $planMethod = $plan->planPaymentMethods()->where(['method' => $method])->first();

        if (empty($planMethod)) {
            $method = $this->paymentMethodRepository->findByMethod($method, $module, true);
            $planId = $this->subscription->createPlanMethod($plan, $method);
        }
    }
}
