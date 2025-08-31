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

class SubscribeResult extends SubscriptionResult
{
    /**
     * @var string|null
     */
    protected ?string $redirectUrl = null;

    public static function make(?string $transactionId = null, string $redirectUrl = null, array $data = []): static
    {
        return new self($transactionId, $redirectUrl, $data);
    }

    public function __construct(?string $transactionId = null, string $redirectUrl = null, array $data = [])
    {
        $this->transactionId = $transactionId;
        $this->redirectUrl = $redirectUrl;
        $this->data = $data;
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
