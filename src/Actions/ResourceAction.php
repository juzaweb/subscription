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
use Juzaweb\Membership\Http\Datatables\PackageDatatable;
use Juzaweb\Subscription\Facades\PaymentMethod;
use Juzaweb\Subscription\Repositories\PlanRepository;

class ResourceAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'registerResources']);

        $this->addAction(
            action_replace(Action::RESOURCE_FORM_LEFT_ACTION, ['name' => 'payment-methods']),
            [$this, 'formPaymentMethod']
        );
    }

    public function registerResources()
    {
        $this->hookAction->addAdminMenu(
            trans('subscription::content.subscription'),
            'subscription',
            [
                'icon' => 'fa fa-users',
                'position' => 30,
            ]
        );

        $this->hookAction->registerResource(
            'plans',
            null,
            [
                'label' => trans('subscription::content.plans'),
                'repository' => PlanRepository::class,
                'datatable' => PackageDatatable::class,
                'menu' => [
                    'icon' => 'fa fa-list-ul',
                    'parent' => 'subscription',
                    'position' => 20,
                ],
                'fields' => [
                    'name' => [
                        'label' => trans('cms::app.name'),
                    ],
                    'price' => [
                        'label' => trans('subscription::content.price'),
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
                'label' => trans('subscription::content.payment_methods'),
                'menu' => [
                    'icon' => 'fa fa-cart',
                    'parent' => 'subscription',
                    'position' => 20,
                ],
                'fields' => [
                    'name' => [
                        'label' => trans('cms::app.name'),
                    ],
                    'description' => [
                        'type' => 'textarea',
                        'label' => trans('cms::app.description'),
                    ]
                ],
                'validator' => [
                    'name' => ['required', 'string', 'max:100'],
                    'method' => ['required', 'string', 'max:100'],
                ],
            ]
        );
    }

    public function formPaymentMethod($model)
    {
        $methods = PaymentMethod::all();

        $methodOptions = $methods->mapWithKeys(fn ($item) => [$item['key'] => $item['label']])->toArray();

        echo e_html(
            view(
                'subscription::payment_method.form',
                compact('model', 'methods', 'methodOptions')
            )->render()
        );
    }
}
