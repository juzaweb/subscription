<?php

namespace Juzaweb\Subscription\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;

/**
 * @method static Collection all()
 */
class PaymentMethod extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PaymentMethodManager::class;
    }
}
