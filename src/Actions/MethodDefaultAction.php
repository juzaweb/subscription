<?php

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\PaymentMethods\Paypal;

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

    public function initActions()
    {
        $this->paymentMethodManager->register(Paypal::class);
    }
}
