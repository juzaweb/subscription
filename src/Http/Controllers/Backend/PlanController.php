<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager;
use Juzaweb\Subscription\Events\CreatePlanSuccess;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;

class PlanController extends BackendController
{
    public function __construct(protected PaymentMethodManager $paymentMethodManager)
    {
    }

    public function createPlan(Request $request, $method): JsonResponse|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $payment = $this->paymentMethodManager->find($method);

            $planId = $payment->createPlan($request->all());

            DB::commit();
        } catch (PaymentMethodException $e) {
            DB::rollBack();
            report($e);
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        event(new CreatePlanSuccess());

        return $this->success(trans('created_plan_success'));
    }

    public function updatePlan(Request $request, $method, $id)
    {
        DB::beginTransaction();
        try {
            $payment = $this->paymentMethodManager->find($method);

            $payment->updatePlan($id, $request->all());

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
