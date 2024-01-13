<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Contrasts;

use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;

/**
 * @see \Juzaweb\Subscription\Support\PaymentResult
 */
interface PaymentResult
{
    public function __construct(
        string $agreementId,
        float $amount,
        string $token,
        string $status = PaymentMethod::STATUS_ACTIVE
    );

    public function withPlan(Plan $plan): static;

    public function withMethod(PaymentMethod $method): static;

    public function withPaymentHistory(PaymentHistory $paymentHistory): static;

    public function setActiveSubscription(bool $canActiveSubscription): static;

    public function canActiveSubscription(): bool;

    public function setMessage(string $message): static;

    public function getMessage(): ?string;

    public function getAgreementId(): string;

    public function getAmount(): float;

    public function getToken(): string;

    public function getStatus(): string;

    public function isActive(): bool;
}
