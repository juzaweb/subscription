<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Http\Datatables\SubscriptionDatatable;
use Juzaweb\Subscription\Models\ModuleSubscription;

class SubscriptionController extends BackendController
{
    use ResourceController;

    protected string $resourceKey = 'module-subscriptions';
    protected string $viewPrefix = 'subscription::backend.subscription';

    public function __construct(protected Subscription $subscription)
    {
    }

    protected function getDataTable(...$params): DataTable
    {
        $dataTable = app(SubscriptionDatatable::class);
        $dataTable->mount($this->resourceKey, null);
        return $dataTable;
    }

    protected function validator(array $attributes, ...$params): ValidatorContract
    {
        return Validator::make(
            $attributes,
            [
                // Rules
            ]
        );
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

    protected function getModel(...$params): string
    {
        return ModuleSubscription::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('subscription::content.user_subscriptions');
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
