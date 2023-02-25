<?php

namespace Juzaweb\Subscription\Support;

use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager as PaymentMethodManagerContrast;

class PaymentMethodManager implements PaymentMethodManagerContrast
{
    public function __construct(protected HookActionContract $hookAction)
    {
    }


}
