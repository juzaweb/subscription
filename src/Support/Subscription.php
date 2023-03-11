<?php

namespace Juzaweb\Subscription\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Juzaweb\CMS\Contracts\GlobalDataContract;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\Subscription as SubscriptionContrasts;
use Juzaweb\Subscription\Events\CreatePlanSuccess;
use Juzaweb\Subscription\Events\UpdatePlanSuccess;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
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

    public function registerModule(string $key, array $args = []): void
    {
        throw_if(empty($args['label']), new SubscriptionException("Option label is required"));

        if (Arr::get($args, 'allow_plans', true)) {
            $this->registerModulePlan($key, $args);
        }

        if (Arr::get($args, 'allow_payment_methods', true)) {
            $this->registerModulePaymentMethod($key, $args);
        }

        if (Arr::get($args, 'allow_user_subscriptions', true)) {
            $this->registerModuleUserSubscription($key, $args);
        }

        if (Arr::get($args, 'allow_payment_histories', true)) {
            $this->registerModulePaymentHistory($key, $args);
        }

        $args = array_merge(['key' => $key], $args);

        $this->globalData->set("subscription_modules.{$key}", new Collection($args));
    }

    public function registerModuleUserSubscription(string $key, array $args = [])
    {
        $this->hookAction->addAdminMenu(
            trans('subscription::content.user_subscriptions'),
            "subscription.{$key}.subscriptions",
            $args['menu'] ?? [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );
    }

    public function registerModulePaymentHistory(string $key, array $args = [])
    {
        $this->hookAction->addAdminMenu(
            trans('subscription::content.payment_histories'),
            "subscription.{$key}.payment-histories",
            $args['menu'] ?? [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );
    }

    public function registerModulePlan(string $key, array $args = []): void
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

    public function registerModulePaymentMethod(string $key, array $args = []): void
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

    public function getModule(string $key = null): Collection
    {
        if ($key) {
            return $this->globalData->get("subscription_modules.{$key}");
        }

        return new Collection($this->globalData->get("subscription_modules"));
    }

    public function createPlanMethod(Plan $plan, int|string|PaymentMethod $method): PlanPaymentMethod
    {
        if (is_numeric($method)) {
            $method = $this->paymentMethodRepository->find($method);
        }

        if (is_string($method)) {
            $method = $this->paymentMethodRepository->findByMethod($method, $plan->module);
        }

        if ($plan->planPaymentMethods()->where(['method_id' => $method])->exists()) {
            throw new PaymentMethodException("Plan already exist.");
        }

        $payment = $this->paymentMethodManager->find($method);

        $planId = $payment->createPlan($plan);

        $planPaymentMethod = $plan->planPaymentMethods()
            ->create(['method' => $method->method, 'payment_plan_id' => $planId, 'method_id' => $method->id]);

        event(new CreatePlanSuccess($plan));

        return $planPaymentMethod;
    }

    public function updatePlanMethod(Plan $plan, int|string|PaymentMethod $method): Plan
    {
        if (is_numeric($method)) {
            $method = $this->paymentMethodRepository->find($method);
        }

        if (is_string($method)) {
            $method = $this->paymentMethodRepository->findByMethod($method, $plan->module);
        }

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
