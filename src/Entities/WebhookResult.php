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

class WebhookResult extends SubscriptionResult
{
    protected string $status;

    public static function make(string $transactionId, string $status, array $data = []): static
    {
        return new self($transactionId, $status, $data);
    }

    public function __construct(string $transactionId, string $status, array $data = [])
    {
        $this->transactionId = $transactionId;

        $this->status = $status;

        $this->data = $data;
    }

    public function isCancel(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
