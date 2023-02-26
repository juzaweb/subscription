<?php

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Http\Controllers\Backend\PaymentMethodController;
use Juzaweb\Subscription\Http\Controllers\Backend\PlanController;

class AjaxAction extends Action
{
    /**
     * Execute the actions.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAdminAjax']);
    }

    public function addAdminAjax()
    {
        $this->hookAction->registerAdminAjax(
            'subscription.payment-config',
            [
                'callback' => [PaymentMethodController::class, 'updatePlan'],
            ]
        );

        $this->hookAction->registerAdminAjax(
            'subscription.update-plan',
            [
                'callback' => [PlanController::class, 'updatePlan'],
                'method' => 'post',
            ]
        );
    }
}
