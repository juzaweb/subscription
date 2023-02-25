<?php

namespace Juzaweb\Subscription\Support;

use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Contrasts\Subscription as SubscriptionContrasts;

class Subscription implements SubscriptionContrasts
{
    public function __construct(protected PaymentMethodManager $paymentMethodManager)
    {
    }
}
