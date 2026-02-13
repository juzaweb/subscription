<?php

/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Core\Http\Requests\BulkActionsRequest;
use Juzaweb\Modules\Subscription\Enums\DurationUnit;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Http\DataTables\PlansDataTable;
use Juzaweb\Modules\Subscription\Http\Requests\PlanRequest;
use Juzaweb\Modules\Subscription\Models\Plan;

class PlanController extends AdminController
{
    public function index(PlansDataTable $dataTable, string $module)
    {
        $dataTable->with(['module' => $module]);
        $createUrl = action([static::class, 'create'], [$module]);

        return $dataTable->render(
            'subscription::plan.index',
            compact('module', 'createUrl')
        );
    }

    public function create(string $module)
    {
        Breadcrumb::add(__('Plans'), admin_url('plans'));

        Breadcrumb::add(__('Create Plan'));

        $locale = $this->getFormLanguage();
        $backUrl = action([static::class, 'index'], [$module]);
        $features = Subscription::features($module);

        return view(
            'subscription::plan.form',
            [
                'model' => new Plan(),
                'action' => action([static::class, 'store'], [$module]),
                'locale' => $locale,
                'backUrl' => $backUrl,
                'features' => $features,
            ]
        );
    }

    public function edit(string $module, string $id)
    {
        Breadcrumb::add(__('Plans'), admin_url('plans'));

        Breadcrumb::add(__('Create Plan'));

        $locale = $this->getFormLanguage();
        $model = Plan::findOrFail($id);
        $backUrl = action([static::class, 'index'], [$module]);
        $features = Subscription::features($module);

        return view(
            'subscription::plan.form',
            [
                'action' => action([static::class, 'update'], [$module, $id]),
                'locale' => $locale,
                'model' => $model,
                'backUrl' => $backUrl,
                'features' => $features,
            ]
        );
    }

    public function store(PlanRequest $request, string $module)
    {
        $features = Subscription::features($module);
        $inputFeatures = $request->input('features', []);

        DB::transaction(
            function () use ($request, $module, $features, $inputFeatures) {
                $data = $request->validated();
                $data['module'] = $module;
                $data['duration'] = 1;
                $data['duration_unit'] = DurationUnit::MONTH;
                if ($data['is_free']) {
                    $data['price'] = 0;
                }

                $model = new Plan();
                $model->fill($data);
                $model->save();

                $featureIds = [];
                foreach ($features as $name => $feature) {
                    if (!isset($inputFeatures[$name])) {
                        continue;
                    }

                    $featureValue = $inputFeatures[$name];
                    $planFeature = $model->features()->updateOrCreate(
                        [
                            'name' => $name,
                        ],
                        [
                            'value' => $featureValue,
                        ]
                    );

                    $featureIds[] = $planFeature->id;
                }

                // Delete removed features
                $model->features()
                    ->whereNotIn('id', $featureIds)
                    ->delete();
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index'], [$module]),
            'message' => __('Plan created successfully'),
        ]);
    }

    public function update(PlanRequest $request, string $module, string $id)
    {
        $model = Plan::findOrFail($id);
        $features = Subscription::features($module);
        $inputFeatures = $request->input('features', []);

        DB::transaction(
            function () use ($request, $id, $model, $module, $features, $inputFeatures) {
                $data = $request->validated();

                unset($data['price'], $data['is_free']);

                $model->update($data);

                $featureIds = [];
                foreach ($features as $name => $feature) {
                    if (!isset($inputFeatures[$name])) {
                        continue;
                    }

                    $featureValue = $inputFeatures[$name];
                    $planFeature = $model->features()->updateOrCreate(
                        [
                            'name' => $name,
                        ],
                        [
                            'value' => $featureValue,
                        ]
                    );

                    $featureIds[] = $planFeature->id;
                }

                // Delete removed features
                $model->features()
                    ->whereNotIn('id', $featureIds)
                    ->delete();
            }
        );

        return $this->success([
            'redirect' => action([static::class, 'index'], [$module]),
            'message' => __('Plan :name updated successfully', ['name' => $model->name]),
        ]);
    }

    public function bulk(BulkActionsRequest $request, string $module)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        $plans = Plan::whereIn('id', $ids)
            ->where('module', $module)
            ->get();

        foreach ($plans as $plan) {
            if ($action === 'activate') {
                $plan->update(['active' => true]);
            } elseif ($action === 'deactivate') {
                $plan->update(['active' => false]);
            }
        }

        return $this->success([
            'message' => __('Bulk action performed successfully'),
        ]);
    }
}
