<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Support;

use Juzaweb\Membership\Models\UserSubscription;
use Juzaweb\Subscription\Contrasts\PaymentResult as PaymentResultContract;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;

class PaymentResult implements PaymentResultContract
{
    protected ?string $message = null;

    public ?Plan $plan = null;

    public ?PaymentMethod $method = null;

    public ?PaymentHistory $paymentHistory = null;

    public bool $canCreateSubscription = false;

    public function __construct(
        protected string $agreementId,
        protected ?float $amount,
        protected string $token,
        protected string $status = UserSubscription::STATUS_ACTIVE
    ) {
    }

    public function withPlan(Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function withMethod(PaymentMethod $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function withPaymentHistory(PaymentHistory $paymentHistory): static
    {
        $this->paymentHistory = $paymentHistory;

        return $this;
    }

    public function setCanCreateSubscription(bool $canCreateSubscription): static
    {
        $this->canCreateSubscription = $canCreateSubscription;

        return $this;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getAgreementId(): string
    {
        return $this->agreementId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Unique for check handle exists
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === UserSubscription::STATUS_ACTIVE;
    }
}
