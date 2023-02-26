<?php

namespace Juzaweb\Subscription\Support;

use Juzaweb\CMS\Contracts\GlobalDataContract;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\Subscription as SubscriptionContrasts;
use Juzaweb\Subscription\Events\CreatePlanSuccess;
use Juzaweb\Subscription\Events\UpdatePlanSuccess;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class Subscription implements SubscriptionContrasts
{
    public function __construct(
        protected PaymentMethodManager    $paymentMethodManager,
        protected PlanRepository          $planRepository,
        protected PaymentMethodRepository $paymentMethodRepository,
        protected GlobalDataContract      $globalData,
        protected HookActionContract      $hookAction
    ) {
    }

    public function registerModule(string $key, array $args = [])
    {
        $this->registerModulePlan($key, $args);

        $this->registerModulePaymentMethod($key, $args);
    }

    public function registerModulePlan(string $key, array $args = [])
    {
        $this->hookAction->addAdminMenu(
            trans('subscription::content.plans'),
            "subscription.{$key}.plans",
            $args['menu'] ?? [
            'icon' => 'fa fa-users',
            'position' => 30,
            ]
        );
    }

    public function registerModulePaymentMethod(string $key, array $args = [])
    {
        $this->hookAction->addAdminMenu(
            trans('subscription::content.payment_methods'),
            "subscription.{$key}.payment-methods",
            $args['menu'] ?? [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );
    }

    public function createPlanMethod(Plan $plan, int $method): Plan
    {
        $method = $this->paymentMethodRepository->find($method);

        if ($plan->planPaymentMethods()->where(['method_id' => $method])->exists()) {
            throw new PaymentMethodException("Plan already exist.");
        }

        $payment = $this->paymentMethodManager->find($method);

        $planId = $payment->createPlan($plan);

        $plan->planPaymentMethods()
            ->create(['method' => $method->method, 'payment_plan_id' => $planId, 'method_id' => $method->id]);

        event(new CreatePlanSuccess($plan));

        return $plan;
    }

    public function updatePlanMethod(Plan $plan, int $method): Plan
    {
        $method = $this->paymentMethodRepository->find($method);

        if (!$planPaymentMethod = $plan->planPaymentMethods()->where(['method_id' => $method])->first()) {
            throw new PaymentMethodException("Plan do not exist exist.");
        }

        $payment = $this->paymentMethodManager->find($method);

        $planId = $payment->updatePlan($plan);

        $planPaymentMethod->update(['method' => $method->method, 'payment_plan_id' => $planId]);

        event(new UpdatePlanSuccess($plan));

        return $plan;
    }
}
