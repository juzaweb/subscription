<?php

namespace Juzaweb\Subscription\Facades;

use Illuminate\Support\Facades\Facade;
use Juzaweb\Subscription\Contrasts\PaymentMethod as PaymentMethodContract;

class PaymentMethod extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PaymentMethodContract::class;
    }
}
