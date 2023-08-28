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

interface PaymentReturnResult
{
    public function __construct(string $agreementId, float $amount, string $token);

    public function getAgreementId(): string;

    public function getAmount(): float;

    public function getToken(): string;
}
