<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Support\Collection;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;

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
     * @param  string  $key
     * @param  array  $args
     * @return void
     * @see \Juzaweb\Subscription\Support\Subscription::registerModule
     */
    public function registerModule(string $key, array $args = []): void;

    public function getModule(string $key = null): Collection;
}
