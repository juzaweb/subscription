<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Support\Collection;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Models\Plan;

interface Subscription
{
    /**
     * Create a plan with payment method
     *
     * @param Plan $plan The Plan object
     * @param int $method The payment method ID
     *
     * @return Plan The Plan object
     *
     * @throws PaymentMethodException If the plan is already exists
     */
    public function createPlanMethod(Plan $plan, int $method): Plan;

    /**
     * Updates plan for payment method.
     *
     * @param Plan $plan The plan to be updated.
     * @param int $method The payment method ID
     * @return Plan The updated plan.
     * @throws PaymentMethodException If the plan payment method does not exist in the database.
     *
     */
    public function updatePlanMethod(Plan $plan, int $method): Plan;

    public function registerModule(string $key, array $args = []): void;

    public function getModule(string $key = null): Collection;
}
