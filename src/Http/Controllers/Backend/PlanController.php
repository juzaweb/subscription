<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
    use ResourceController;

    protected ?Collection $moduleSetting;
    protected string $resourceKey = 'subscription-plans';
    protected string $viewPrefix = 'cms::backend.resource';

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

    protected function beforeSave(&$data, &$model, ...$params)
    {
        if (isset($data['price'])) {
            $data['price'] = parse_price_format($data['price']);
        }
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

    protected function getDataTable(...$params)
    {
        $dataTable = app(PlanDatatable::class);
        $dataTable->mount($this->resourceKey, null);
        return $dataTable;
    }

    protected function validator(array $attributes, ...$params): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'string'],
            'is_free' => ['required', 'numeric', 'in:0,1'],
            'enable_trial' => ['required', 'numeric', 'in:0,1'],
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
