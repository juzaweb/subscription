<?php

namespace Juzaweb\Subscription\Support;

use Illuminate\Support\Arr;
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

    /**
     * @throws Throwable
     */
    public function register(string $method, array $args = []): void
    {
        $defaults = [
            'key' => $method,
            'label' => null,
            'class' => null,
            'configs' => [],
        ];

        throw_unless(Arr::get($args, 'class'), new \Exception('Class helper Payment Method is required.'));

        $args = array_merge($defaults, $args);

        $this->globalData->set("subscription_methods.{$method}", new Collection($args));
    }

    public function find(string $method): PaymentMethod
    {
        //
    }
}
