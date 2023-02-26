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
use Juzaweb\Subscription\Facades\PaymentMethod;
use Juzaweb\Subscription\Http\Datatables\PackageDatatable;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class ResourceAction extends Action
{
    public function handle(): void
    {
        $this->addAction(Action::INIT_ACTION, [$this, 'registerResources']);

        $this->addAction(Action::BACKEND_INIT, [$this, 'enqueueStyles']);

        /*$this->addAction(
            action_replace(Action::RESOURCE_FORM_LEFT_ACTION, ['name' => 'payment-methods']),
            [$this, 'formPaymentMethod']
        );*/
    }

    public function registerResources()
    {
        $this->hookAction->registerResource(
            'plans',
            null,
            [
                'label' => trans('subscription::content.plans'),
                'repository' => PlanRepository::class,
                'datatable' => PackageDatatable::class,
                'menu' => null,
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
                'repository' => PaymentMethodRepository::class,
                'menu' => null,
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

    public function enqueueStyles()
    {
        $this->hookAction->enqueueScript(
            'subs-js',
            plugin_asset('js/admin-script.js', 'juzaweb/subscription')
        );
    }
}
