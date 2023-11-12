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

use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Models\UserSubscription;

class PaymentReturn implements PaymentReturnResult
{
    public function __construct(
        protected string $agreementId,
        protected float $amount,
        protected string $token,
        protected string $status = UserSubscription::STATUS_ACTIVE
    ) {
    }

    public function getAgreementId(): string
    {
        return $this->agreementId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

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
