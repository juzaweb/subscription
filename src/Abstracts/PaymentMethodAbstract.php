<?php

namespace Juzaweb\Subscription\Abstracts;

/**
 * @property string $name
 */
abstract class PaymentMethodAbstract
{
    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->name);
    }

    public function getConfigs(): array
    {
        return [];
    }
}
