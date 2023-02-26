<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Juzaweb\CMS\Facades\Field;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Facades\PaymentMethod;
use Juzaweb\Subscription\Http\Datatables\PaymentMethodDatatable;

class PaymentMethodController extends BackendController
{
    use ResourceController;

    protected string $viewPrefix = 'subscription::payment_method';

    public function getConfigs(Request $request): \Illuminate\Http\JsonResponse
    {
        $method = $request->input('method');
        $config = PaymentMethod::all()->get($method);
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

    protected function getModel(...$params)
    {
        // TODO: Implement getModel() method.
    }

    protected function getTitle(...$params)
    {
        // TODO: Implement getTitle() method.
    }
}
