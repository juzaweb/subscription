<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Support\Collection;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;

/**
 * @see \Juzaweb\Subscription\Support\Subscription
 */
interface Subscription
{
    /**
     * Create a plan with payment method
     *
     * @param  Plan  $plan  The Plan object
     * @param  int|PaymentMethod  $method  The payment method ID
     *
     * @return PlanPaymentMethod The Payment Method model
     *
     * @throws PaymentMethodException If the plan is already exists
     * @see \Juzaweb\Subscription\Support\Subscription::createPlanMethod
     */
    public function createPlanMethod(Plan $plan, int|PaymentMethod $method): PlanPaymentMethod;

    /**
     * Updates plan for payment method.
     *
     * @param  Plan  $plan  The plan to be updated.
     * @param  int|PaymentMethod  $method  The payment method ID
     * @return Plan The updated plan.
     * @throws PaymentMethodException If the plan payment method does not exist in the database.
     * @see \Juzaweb\Subscription\Support\Subscription::updatePlanMethod
     */
    public function updatePlanMethod(Plan $plan, int|PaymentMethod $method): Plan;

    /**
     * Registers a module with the given key and arguments.
     *
     * @param  string  $key  The key of the module.
     * @param  array  $args  The arguments for the module.
     *   - label (string, required): The label for the module.
     *   - allow_plans (bool, optional): Whether to allow plans for the module. Defaults to true.
     *   - allow_payment_methods (bool, optional): Whether to allow payment methods for the module. Defaults to true.
     *   - allow_user_subscriptions (bool, optional): Whether to allow user subscriptions for the module.
     * Defaults to true.
     *   - allow_payment_histories (bool, optional): Whether to allow payment histories for the module.
     * Defaults to true.
     *   - allow_setting_page (bool, optional): Whether to allow a setting page for the module. Defaults to true.
     * @return void
     * @throws SubscriptionException If the option label is empty.
     */
    public function registerModule(string $key, array $args = []): void;

    /**
     * Registers a plan feature.
     *
     * @param  string  $key  The key of the plan feature.
     * @param  array  $args  An optional array of arguments.
     *                    - label: The label of the option (required).
     *                    - module: The module of the plan feature.
     *                    - key: The key of the plan feature.
     * @return void
     * @throws SubscriptionException If the option label is empty.
     */
    public function registerPlanFeature(string $key, array $args = []): void;

    /**
     * Retrieves the plan features for a specific module or all modules.
     *
     * @param  string|null  $module  The module name to filter the features by.
     *                            If null, all features will be returned.
     * @return Collection The collection of plan features.
     */
    public function getPlanFeatures(string $module = null): Collection;

    public function getModule(string $key = null): Collection;
}
