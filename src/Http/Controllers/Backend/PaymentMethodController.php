<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Juzaweb\CMS\Facades\Field;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Subscription\Facades\PaymentMethod;
use Juzaweb\Subscription\Http\Datatables\PaymentMethodDatatable;
use Juzaweb\Subscription\Models\PaymentMethod as PaymentMethodModel;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodController extends BackendController
{
    protected ?Collection $moduleSetting;
    protected string $resourceKey = 'subscription-payment-methods';
    protected string $viewPrefix = 'subscription::backend.payment_method';

    use ResourceController {
        getDataForForm as DataForForm;
    }

    public function __construct(protected Subscription $subscription)
    {
    }

    public function getConfigs(Request $request): JsonResponse
    {
        $method = $request->input('method');
        $fields = $this->getConfigFields($method);

        $html = Field::render($fields)->render();

        return response()->json(
            [
                'html' => $html,
            ]
        );
    }

    public function callAction($method, $parameters): Response|View
    {
        $params = collect($parameters)->filter(fn($item) => is_string($item))->values()->toArray();

        throw_unless(
            $this->getSettingModule(...$params),
            new SubscriptionException('Module not found.')
        );

        return parent::callAction($method, $parameters);
    }

    protected function getBreadcrumbPrefix(...$params)
    {
        $this->addBreadcrumb(
            [
                'title' => $this->getSettingModule(...$params)->get('label'),
                'url' => '#',
            ]
        );
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
        $data['module'] = $this->getSettingModule(...$params)->get('key');
        $data['config_fields'] = $this->getConfigFields($model->method);
        return $data;
    }

    protected function getConfigFields(?string $method): array
    {
        $config = PaymentMethod::all()->get($method, new Collection());

        return collect($config->get('configs', []))->map(
            function ($item, $key) {
                $item['name'] = "configs[{$key}]";
                return $item;
            }
        )->toArray();
    }

    protected function getDataTable(...$params)
    {
        $dataTable = app(PaymentMethodDatatable::class);
        $dataTable->mount($this->resourceKey, null);
        return $dataTable;
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
        return PaymentMethodModel::class;
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

        $this->setting = HookAction::getResource($this->resourceKey);

        return $this->setting;
    }
}
