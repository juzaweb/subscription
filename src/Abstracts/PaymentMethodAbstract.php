<?php

namespace Juzaweb\Subscription\Abstracts;

use Juzaweb\Subscription\Contrasts\PaymentResult as PaymentResultContract;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Support\PaymentResult;

/**
 * @property string $name
 */
abstract class PaymentMethodAbstract
{
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

    abstract public function getConfigs(): array;

    abstract public function return(PlanModel $plan, array $data): PaymentResultContract;

    public function cancel(): bool
    {
        return true;
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
        ?float $amount,
        string $token,
        string $status = ModuleSubscription::STATUS_ACTIVE
    ): PaymentResultContract {
        return new PaymentResult($agreementId, $amount, $token, $status);
    }
}
