<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Support\Entities;

class SubscribeResult extends ResultEntity
{
    protected string $redirectUrl;

    protected bool $isRedirect = false;

    protected array $metas = [];

    protected array $data = [];

    public function __construct(
        public string $id
    ) {
    }

    public function getMetas(): array
    {
        return $this->metas;
    }

    public function setMetas(array $meta): static
    {
        $this->metas = $meta;

        return $this;
    }

    public function withData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function isRedirect(): bool
    {
        return $this->isRedirect;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): static
    {
        $this->isRedirect = true;
        $this->redirectUrl = $redirectUrl;

        return $this;
    }
}
