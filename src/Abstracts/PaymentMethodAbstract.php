<?php

namespace Juzaweb\Subscription\Abstracts;

use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Support\PaymentReturn;

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

    public function getConfigs(): array
    {
        return [];
    }

    public function isRedirect(): bool
    {
        return $this->isRedirect ?? true;
    }

    public function cancel(): bool
    {
        return true;
    }

    protected function makePaymentReturnResult(string $agreementId, float $amount, string $token): PaymentReturnResult
    {
        return new PaymentReturn($agreementId, $amount, $token);
    }
}
