<?php

namespace Juzaweb\Subscription\Actions;

use Juzaweb\CMS\Abstracts\Action;
use Juzaweb\Membership\Http\Datatables\PackageDatatable;
use Juzaweb\Subscription\Repositories\PlanRepository;

class ResourceAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'registerResources']);
    }

    public function registerResources()
    {
        $this->hookAction->addAdminMenu(
            trans('membership::content.membership'),
            'membership',
            [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );

        $this->hookAction->registerResource(
            'plans',
            null,
            [
                'label' => trans('membership::content.plans'),
                'repository' => PlanRepository::class,
                'datatable' => PackageDatatable::class,
                'menu' => [
                    'icon' => 'fa fa-list-ul',
                    'parent' => 'membership',
                    'position' => 20,
                ],
                'fields' => [
                    'name' => [
                        'label' => trans('cms::app.name'),
                    ],
                    'price' => [
                        'label' => trans('membership::content.price'),
                    ]
                ],
                'validator' => [
                    'name' => ['required', 'string', 'max:100'],
                    'price' => ['required', 'numeric', 'min:0'],
                ],
            ]
        );

        $this->hookAction->registerResource(
            'payment-methods',
            null,
            [
                'label' => trans('membership::content.payment_methods'),
                'menu' => [
                    'icon' => 'fa fa-cart',
                    'parent' => 'membership',
                    'position' => 20,
                ],
                'fields' => [
                    'name' => [
                        'label' => trans('cms::app.name'),
                    ],
                    'method' => [
                        'label' => trans('membership::content.method'),
                        'type' => 'select',
                        'data' => [
                            'id' => 'select-payment-method',
                        ]
                    ],
                ],
                'validator' => [
                    'name' => ['required', 'string', 'max:100'],
                    'method' => ['required', 'string', 'max:100'],
                ],
            ]
        );
    }
}
