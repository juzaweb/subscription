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

use Juzaweb\Subscription\Models\UserSubscription;

interface PaymentReturnResult
{
    public function __construct(
        string $agreementId,
        float $amount,
        string $token,
        string $status = UserSubscription::STATUS_ACTIVE
    );

    public function getAgreementId(): string;

    public function getAmount(): float;

    public function getToken(): string;

    public function getStatus(): string;

    public function isActive(): bool;
}
