<?php

namespace Juzaweb\Subscription\Http\Controllers\Frontend;

use Juzaweb\CMS\Http\Controllers\FrontendController;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Http\Requests\Frontend\PaymentRequest;
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

    public function payment(PaymentRequest $request)
    {
        $method = $request->input('method');
        $module = $request->input('module');

        $plan = $this->planRepository->find($request->input('plan_id'));
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


}
