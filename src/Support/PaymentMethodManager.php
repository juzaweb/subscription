<?php

namespace Juzaweb\Subscription\Support;

use Illuminate\Support\Collection;
use Juzaweb\CMS\Contracts\GlobalDataContract;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager as PaymentMethodManagerContrast;
use Throwable;

class PaymentMethodManager implements PaymentMethodManagerContrast
{
    public function __construct(protected HookActionContract $hookAction, protected GlobalDataContract $globalData)
    {
    }

    public function all(): Collection
    {
        return collect($this->globalData->get("subscription_methods"));
    }

    /**
     * @throws Throwable
     */
    public function register(string|PaymentMethod $method): void
    {
        if (!$method instanceof PaymentMethod) {
            $method = app($method);
        }

        $args = [
            'key' => $method->getName(),
            'label' => $method->getLabel(),
            'class' => get_class($method),
            'configs' => $method->getConfigs(),
        ];

        $this->globalData->set("subscription_methods.{$args['key']}", new Collection($args));
    }

    public function find(string $method): PaymentMethod
    {
        //
    }
}
