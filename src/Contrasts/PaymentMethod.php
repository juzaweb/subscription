<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Http\Request;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Support\Entities\CreatedPlanResult;
use Juzaweb\Subscription\Support\Entities\SubscribeResult;

/**
 * @see \Juzaweb\Subscription\Support\PaymentMethods\Paypal
 */
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

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): SubscribeResult;

    public function cancel(): bool;

    public function webhook(Request $request): bool|PaymentResult;

    /**
     * Create a plan
     *
     * @return string - identity plan id
     */
    public function createPlan(Plan $plan): CreatedPlanResult;

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string;

    public function return(Plan $plan, array $data): PaymentResult;
}
