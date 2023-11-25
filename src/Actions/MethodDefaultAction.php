<?php

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Support\PaymentMethods\Paypal;
use Juzaweb\Subscription\Support\PaymentMethods\Stripe;

class MethodDefaultAction extends Action
{
    public function __construct(protected PaymentMethodManager $paymentMethodManager)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'initActions']);
    }

    public function initActions(): void
    {
        $this->paymentMethodManager->register('paypal', Paypal::class);

        //$this->paymentMethodManager->register('stripe', Stripe::class);
    }
}
