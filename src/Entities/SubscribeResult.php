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

class SubscribeResult
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

    /**
     * @var string|null
     */
    protected ?string $redirectUrl = null;

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

    public function isRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }

    public function setRedirectUrl(string $url): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
