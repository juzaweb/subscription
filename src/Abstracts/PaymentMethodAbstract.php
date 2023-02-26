<?php

namespace Juzaweb\Subscription\Abstracts;

use Juzaweb\Subscription\Models\Plan;

/**
 * @property string $name
 */
abstract class PaymentMethodAbstract
{
    public function __construct(protected Plan $plan)
    {
    }

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
