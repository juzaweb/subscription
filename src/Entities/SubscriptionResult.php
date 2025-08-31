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

use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;

abstract class SubscriptionResult
{
    /**
     * @var null|string
     */
    protected ?string $transactionId = null;

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var bool
     */
    protected bool $isSuccessful = false;

    protected ?SubscriptionHistory $subscriptionHistory = null;

    public function setSuccessful(bool $isSuccessful): self
    {
        $this->isSuccessful = $isSuccessful;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function setTransactionId(string $transactionId): self
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setSubscriptionHistory(SubscriptionHistory $history): self
    {
        $this->subscriptionHistory = $history;

        return $this;
    }

    public function getSubscriptionHistory(): SubscriptionHistory
    {
        return $this->subscriptionHistory;
    }
}
