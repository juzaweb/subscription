<?php

namespace Juzaweb\Subscription\Contrasts;

use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\PlanPaymentMethod;

interface PaymentMethod
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return array
     */
    public function getConfigs(): array;

    public function isRedirect(): bool;

    public function getRedirectUrl(PlanPaymentMethod $planPaymentMethod): string;

    /**
     * Create a plan
     *
     * @return string - identity plan id
     */
    public function createPlan(Plan $plan): string;

    public function return(Plan $plan, array $data): PaymentReturnResult;
}
