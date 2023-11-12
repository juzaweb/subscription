<?php

namespace Juzaweb\Subscription\Abstracts;

use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\UserSubscription;
use Juzaweb\Subscription\Support\PaymentReturn;

/**
 * @property string $name
 */
abstract class PaymentMethodAbstract
{
    protected ?string $redirectUrl = null;
    protected bool $isRedirect = true;

    public function __construct(protected PaymentMethod $paymentMethod)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->name);
    }

    public function getConfigs(): array
    {
        return [];
    }

    public function isRedirect(): bool
    {
        return $this->isRedirect;
    }

    public function return(PlanModel $plan, array $data): ?PaymentReturnResult
    {
        return null;
    }

    public function cancel(): bool
    {
        return true;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    protected function getReturnUrl(Plan $plan): string
    {
        return route('subscription.module.return', [$plan->module, $plan->uuid, $this->name]);
    }

    protected function getCancelUrl(Plan $plan): string
    {
        return route('subscription.module.cancel', [$plan->module, $plan->uuid, $this->name]);
    }

    protected function makePaymentReturnResult(
        string $agreementId,
        float $amount,
        string $token,
        string $status = UserSubscription::STATUS_ACTIVE
    ): PaymentReturnResult {
        return new PaymentReturn($agreementId, $amount, $token, $status);
    }
}
