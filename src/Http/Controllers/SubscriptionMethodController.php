<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Http\DataTables\SubscriptionMethodsDataTable;
use Juzaweb\Modules\Subscription\Http\Requests\SubscriptionMethodRequest;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;

class SubscriptionMethodController extends AdminController
{
    public function index(SubscriptionMethodsDataTable $dataTable)
    {
        Breadcrumb::add(__('Subscription Methods'));

        $testPlans = Plan::where(['module' => 'test'])
            ->get()
            ->mapWithKeys(fn($plan) => [$plan->id => $plan->name . ' ($' . $plan->price . ')']);
        $paymentMethods = SubscriptionMethod::withTranslation()->get()
            ->mapWithKeys(fn($method) => [$method->id => $method->name]);

        return $dataTable->render(
            'subscription::method.index',
            compact('testPlans', 'paymentMethods')
        );
    }

    public function create()
    {
        Breadcrumb::add(__('Subscription Methods'), admin_url('subscription-methods'));

        Breadcrumb::add(__('Create Subscription Method'));

        $locale = $this->getFormLanguage();
        $drivers = Subscription::drivers()->map(fn($driver) => $driver->getName());

        return view(
            'subscription::method.form',
            [
                'model' => new SubscriptionMethod(),
                'action' => action([static::class, 'store']),
                'locale' => $locale,
                'drivers' => $drivers,
            ]
        );
    }

    public function edit(string $id)
    {
        Breadcrumb::add(__('Subscription Methods'), admin_url('subscription-methods'));

        Breadcrumb::add(__('Create Subscription Method'));

        $locale = $this->getFormLanguage();
        $model = SubscriptionMethod::withTranslation($locale)->findOrFail($id);
        $model?->setDefaultLocale($locale);
        $drivers = Subscription::drivers()->map(fn($driver) => $driver->getName());

        return view(
            'subscription::method.form',
            [
                'action' => action([static::class, 'update'], [$id]),
                'locale' => $locale,
                'drivers' => $drivers,
                'model' => $model,
            ]
        );
    }

    public function store(SubscriptionMethodRequest $request)
    {
        $locale = $this->getFormLanguage();
        $model = new SubscriptionMethod($request->validated());
        $model->setDefaultLocale($locale);
        $model->save();

        return $this->success([
            'redirect' => action([static::class, 'index']),
        ]);
    }

    public function update(SubscriptionMethodRequest $request, string $id)
    {
        $locale = $this->getFormLanguage();
        $model = SubscriptionMethod::findOrFail($id);
        $model->setDefaultLocale($locale);
        $model->update($request->validated());

        return $this->success([
            'redirect' => action([static::class, 'index']),
        ]);
    }

    public function getData(string $driver): JsonResponse
    {
        return response()->json([
            'config' => Subscription::renderConfig($driver),
        ]);
    }

    public function updateSandbox(Request $request): JsonResponse
    {
        $sandbox = (int) $request->post('sandbox');

        setting()->set('subscription_sandbox', $sandbox);

        return $this->success([
            'message' => __('Update sandbox setting successfully.'),
        ]);
    }
}
