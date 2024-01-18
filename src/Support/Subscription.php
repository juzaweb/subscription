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
use Juzaweb\Subscription\Http\Datatables\SubscriptionDatatable;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Repositories\ModuleSubscriptionRepository;
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

    public function createPlanMethod(Plan $plan, int|string|PaymentMethod $method): PlanPaymentMethod
    {
        if (is_numeric($method)) {
            $method = $this->paymentMethodRepository->find($method);
        }

        if (is_string($method)) {
            $method = $this->paymentMethodRepository->findByMethod($method, $plan->module);
        }

        if ($plan->planPaymentMethods()->where(['method_id' => $method->id])->exists()) {
            throw new PaymentMethodException("Plan already exist.");
        }

        $payment = $this->paymentMethodManager->find($method);

        $paymentPlan = $payment->createPlan($plan);

        /**
         * @var PlanPaymentMethod $planPaymentMethod
         */
        $planPaymentMethod = $plan->planPaymentMethods()
            ->create(
                [
                    'method' => $method->method,
                    'payment_plan_id' => $paymentPlan->id,
                    'method_id' => $method->id,
                    'metas' => $paymentPlan->getMetas(),
                ]
            );

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

        /**
         * @var PlanPaymentMethod|null $planPaymentMethod
         */
        $planPaymentMethod = $plan->planPaymentMethods()->where(['method_id' => $method->id])->first();

        if ($planPaymentMethod === null) {
            $this->createPlanMethod($plan, $method);

            return $plan;
        }

        $payment = $this->paymentMethodManager->find($method);

        $payment->updatePlan($plan, $planPaymentMethod);

        $planPaymentMethod->touch();

        event(new UpdatePlanSuccess($plan));

        return $plan;
    }

    public function registerModule(string $key, array $args = []): void
    {
        throw_if(empty($args['label']), new SubscriptionException("Option label is required"));

        throw_if(empty($args['model']), new SubscriptionException("Option model is required"));

        if (Arr::get($args, 'allow_plans', true)) {
            $this->registerModulePlan($key, $args);
        }

        if (Arr::get($args, 'allow_payment_methods', true)) {
            $this->registerModulePaymentMethod($key, $args);
        }

        if (Arr::get($args, 'allow_payment_histories', true)) {
            $this->registerModulePaymentHistory($key, $args);
        }

        if (Arr::get($args, 'allow_user_subscriptions', true)) {
            $this->registerModuleSubscription($key, $args);
        }

        if (Arr::get($args, 'allow_setting_page', true)) {
            $this->registerSettingPage($key, $args);
        }

        $args = array_merge(['key' => $key], $args);

        $this->globalData->set("subscription_modules.{$key}", new Collection($args));
    }

    public function registerModuleSubscription(string $key, array $args = []): void
    {
        $this->hookAction->registerResource(
            "module-subscriptions",
            null,
            [
                'label' => trans('membership::content.user_subscriptions'),
                'repository' => ModuleSubscriptionRepository::class,
                'datatable' => SubscriptionDatatable::class,
                'menu' => null,
            ]
        );

        $this->hookAction->addAdminMenu(
            trans('subscription::content.subscriptions'),
            "subscription.{$key}.subscriptions",
            $args['menu'] ?? [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );
    }

    public function registerPlanFeature(string $key, array $args = []): void
    {
        throw_if(empty($args['label']), new SubscriptionException("Option label is required"));

        $defaults = [
            'module' => null,
            'key' => $key,
            'require_value' => false,
            'default_value' => null,
        ];

        $args = array_merge($defaults, $args);

        $this->globalData->set("subscription_plan_features.{$key}", new Collection($args));
    }

    public function getPlanFeatures(string $module = null): Collection
    {
        $features = collect($this->globalData->get('subscription_plan_features'));

        if ($module) {
            return $features->filter(function ($feature) use ($module) {
                return $feature['module'] === null || $feature['module'] === $module;
            });
        }

        return $features;
    }

    public function registerSettingPage(string $key, array $args = []): void
    {
        $this->hookAction->registerSettingPage(
            'subscription',
            [
                'label' => trans('cms::app.setting'),
                'menu' => $args['menu'] ?? [
                    'icon' => 'fa fa-cogs',
                    'position' => 30,
                ],
            ]
        );

        $this->hookAction->addSettingForm(
            'subscription',
            [
                'name' => trans('subscription::content.subscription'),
                'page' => 'subscription',
                'priority' => 99,
            ]
        );

        $this->hookAction->registerConfig(
            [
                "subscription_{$key}_enable_trial" => [
                    'type' => 'select',
                    'label' => trans('subscription::content.enable_trial'),
                    'form' => 'subscription',
                    'data' => [
                        'options' => [
                            0 => trans('cms::app.disabled'),
                            1 => trans('cms::app.enabled'),
                        ]
                    ]
                ],
                "subscription_{$key}_free_trial_days" => [
                    'label' => trans('subscription::content.free_trial_days'),
                    'form' => 'subscription',
                    'data' => [
                        'class' => 'is-number'
                    ]
                ],
            ]
        );
    }

    public function registerModulePaymentHistory(string $key, array $args = []): void
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
}
