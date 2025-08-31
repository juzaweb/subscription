<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Entities;

class SubscriptionReturnResult extends SubscriptionResult
{
    public static function make(string $transactionId, array $data = []): static
    {
        return new self($transactionId, $data);
    }

    public function __construct(string $transactionId, array $data = [])
    {
        $this->transactionId = $transactionId;
        $this->data = $data;
    }
}
