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

class PaymentReturn implements PaymentReturnResult
{
    public function __construct(protected string $agreementId, protected float $amount, protected string $token)
    {
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
}
