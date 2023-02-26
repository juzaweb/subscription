<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Http\Requests\Plan\UpdatePlanRequest;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Juzaweb\Subscription\Repositories\PlanRepository;

class PlanController extends BackendController
{
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

        return $this->success(trans('created_plan_success'));
    }
}
