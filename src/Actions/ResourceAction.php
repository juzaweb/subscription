<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/juzacms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Subscription\Http\Datatables\PlanDatatable;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class ResourceAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'registerResources']);

        $this->addAction(Action::BACKEND_INIT, [$this, 'enqueueStyles']);
    }

    public function registerResources()
    {
        $this->hookAction->registerResource(
            'subscription-plans',
            null,
            [
                'label' => trans('subscription::content.plans'),
                'repository' => PlanRepository::class,
                'datatable' => PlanDatatable::class,
                'menu' => null,
                'fields' => [
                    'name' => [
                        'label' => trans('cms::app.name'),
                    ],
                    'price' => [
                        'label' => trans('subscription::content.price'),
                        'data' => [
                            'class' => 'is-number number-format'
                        ]
                    ],
                ],
                'validator' => [
                    'name' => ['required', 'string', 'max:100'],
                    'price' => ['required', 'numeric', 'min:0'],
                ],
            ]
        );

        $this->hookAction->registerResource(
            'subscription-payment-methods',
            null,
            [
                'label' => trans('subscription::content.payment_methods'),
                'repository' => PaymentMethodRepository::class,
                'menu' => null,
                'validator' => [
                    'name' => ['required', 'string', 'max:100'],
                    'method' => ['required', 'string', 'max:100'],
                ],
            ]
        );
    }

    public function enqueueStyles()
    {
        $this->hookAction->enqueueScript(
            'subs-js',
            plugin_asset('js/admin-script.js', 'juzaweb/subscription')
        );
    }
}
