<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Http\Resources\PaymentMethodResource;
use Juzaweb\Subscription\Http\Resources\PlanResource;
use Juzaweb\Subscription\Models\PaymentMethod;
use Juzaweb\Subscription\Models\Plan;

class PageDataAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'pageCustomDatas']);
    }

    public function pageCustomDatas(): void
    {
        $this->registerPageCustomData(
            'subscription_payment_methods',
            fn ($request, $option) =>
                PaymentMethodResource::collection(PaymentMethod::where(['module' => $option['module'] ?? 'membership'])->get())
                ->toArray(request())
        );

        $this->registerPageCustomData(
            'subscription_plans',
            fn ($request, $option) =>
                PlanResource::collection(Plan::with(['features'])
                    ->whereIsActive()
                    ->where(['module' => $option['module'] ?? 'membership'])->get()
                )
                    ->toArray($request)
        );
    }
}
