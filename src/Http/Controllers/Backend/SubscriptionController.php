<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;

class SubscriptionController extends BackendController
{
    public function __construct(protected PaymentMethodManager $paymentMethodManager)
    {
    }
}
