<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Subscription\Http\Datatables\PlanDatatable;
use Juzaweb\Subscription\Http\Requests\Plan\UpdatePlanRequest;
use Juzaweb\Subscription\Models\Plan;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends BackendController
{
    use ResourceController {
        getDataForForm as DataForForm;
        parseDataForSave as DataForSave;
    }

    protected ?Collection $moduleSetting;
    protected string $resourceKey = 'subscription-plans';
    protected string $viewPrefix = 'subscription::backend.plan';

    public function __construct(
        protected PlanRepository $planRepository,
        protected PaymentMethodRepository $paymentMethodRepository,
        protected Subscription $subscription
    ) {
    }

    public function updatePlan(UpdatePlanRequest $request): JsonResponse|RedirectResponse
    {
        $method = $request->input('method_id');
        $planId = $request->input('plan_id');

        $plan = $this->planRepository->find($planId);

        DB::beginTransaction();
        try {
            $this->subscription->updatePlanMethod($plan, $method);

            DB::commit();
        } catch (PaymentMethodException $e) {
            DB::rollBack();
            report($e);
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->success(trans('subscription::content.created_plan_success'));
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

    protected function getDataForForm(Model $model, ...$params): array
    {
        $data = $this->DataForForm($model, ...$params);
        $data['module'] = $this->getSettingModule(...$params)->get('key');
        return $data;
    }

    protected function beforeSave(&$data, &$model, ...$params): void
    {
        if (isset($data['price'])) {
            $data['price'] = parse_price_format($data['price']);
        }
    }

    protected function getBreadcrumbPrefix(...$params): void
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

    protected function getDataTable(...$params): \Juzaweb\CMS\Abstracts\DataTable
    {
        $dataTable = app(PlanDatatable::class);
        $dataTable->mount($this->resourceKey, null);
        return $dataTable;
    }

    protected function parseDataForSave(array $attributes, ...$params): array
    {
        $data = $this->DataForSave($attributes, ...$params);
        $data['price'] = parse_price_format(Arr::get($attributes, 'price', 0));
        $data['is_free'] = Arr::get($attributes, 'is_free', 0);
        $data['enable_trial'] = Arr::get($attributes, 'enable_trial', 0);
        $data['free_trial_days'] = (int) Arr::get($attributes, 'free_trial_days', 0);

        if ($data['is_free']) {
            $data['price'] = 0;
        }

        return $data;
    }

    protected function validator(array $attributes, ...$params): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['nullable', 'string'],
            'is_free' => ['nullable', 'numeric', 'in:1'],
            'enable_trial' => ['nullable', 'numeric', 'in:1'],
            'free_trial_days' => ['nullable', 'numeric', 'min:0'],
            'module' => ['required'],
        ];
    }

    protected function getModel(...$params): string
    {
        return Plan::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('subscription::content.plans');
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
