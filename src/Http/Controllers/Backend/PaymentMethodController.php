<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Juzaweb\CMS\Facades\Field;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Subscription\Facades\PaymentMethod;
use Juzaweb\Subscription\Http\Datatables\PaymentMethodDatatable;

class PaymentMethodController extends BackendController
{
    protected ?Collection $moduleSetting;

    use ResourceController {
        getDataForForm as DataForForm;
    }

    public function __construct(protected Subscription $subscription)
    {
    }

    protected string $viewPrefix = 'subscription::payment_method';

    public function getConfigs(Request $request): \Illuminate\Http\JsonResponse
    {
        $method = $request->input('method');
        $config = PaymentMethod::all()->get($method, new Collection());
        $fields = collect($config->get('configs', []))->map(
            function ($item, $key) {
                $item['name'] = "configs[{$key}]";
                return $item;
            }
        )->toArray();

        $html = Field::render($fields)->render();

        return response()->json(
            [
                'html' => $html,
            ]
        );
    }

    public function callAction($method, $parameters)
    {
        throw_unless(
            $this->getSettingModule(...array_values($parameters)),
            new SubscriptionException('Module not found.')
        );

        return parent::callAction($method, $parameters);
    }

    protected function getSettingModule(...$params): Collection
    {
        if (isset($this->moduleSetting)) {
            return $this->moduleSetting;
        }

        $this->moduleSetting = $this->subscription->getModule($params[0]);

        return $this->moduleSetting;
    }

    protected function getDataForForm($model, ...$params): array
    {
        $methods = PaymentMethod::all();
        $methodOptions = $methods->mapWithKeys(fn ($item) => [$item['key'] => $item['label']])->toArray();

        $data = $this->DataForForm($model, ...$params);
        $data['methods'] = $methods;
        $data['methodOptions'] = $methodOptions;
        return $data;
    }

    protected function getDataTable(...$params)
    {
        return app(PaymentMethodDatatable::class);
    }

    protected function validator(array $attributes, ...$params): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'method' => ['required', 'string', 'max:100'],
        ];
    }

    protected function getModel(...$params): string
    {
        return \Juzaweb\Subscription\Models\PaymentMethod::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('subscription::content.payment_methods');
    }

    protected function getSetting(...$params): Collection
    {
        if (isset($this->setting)) {
            return $this->setting;
        }

        $this->setting = HookAction::getResource('subscription-payment-methods');

        return $this->setting;
    }
}
