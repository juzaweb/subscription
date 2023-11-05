<?php

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Http\Controllers\Backend\PaymentMethodController;
use Juzaweb\Subscription\Http\Controllers\Backend\PlanController;
use Juzaweb\Subscription\Http\Controllers\Frontend\PaymentController;

class AjaxAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::BACKEND_INIT, [$this, 'addAdminAjax']);
        //$this->addAction(Action::FRONTEND_INIT, [$this, 'addFrontendAjax']);
    }

    public function addFrontendAjax(): void
    {
        $this->hookAction->registerFrontendAjax(
            'subscription.payment',
            [
                //'method' => 'post',
                'callback' => [PaymentController::class, 'payment'],
            ]
        );
    }

    public function addAdminAjax(): void
    {
        $this->hookAction->registerAdminAjax(
            'subscription.payment-config',
            [
                'callback' => [PaymentMethodController::class, 'getConfigs'],
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
