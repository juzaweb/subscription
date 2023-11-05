<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Http\Request;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\Plan as PlanModel;
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

    public function getRedirectUrl(): ?string;

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): bool;

    public function cancel(): bool;

    public function setRedirectUrl(string $redirectUrl): void;

    public function webhook(Request $request): bool|PaymentReturnResult;

    /**
     * Create a plan
     *
     * @return string - identity plan id
     */
    public function createPlan(Plan $plan): string;

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string;

    public function return(Plan $plan, array $data): ?PaymentReturnResult;
}
