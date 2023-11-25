<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\CMS\Traits\ResourceController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Http\Datatables\UserSubscriptionDatatable;
use Juzaweb\Subscription\Models\UserSubscription;

class UserSubscriptionController extends BackendController
{
    use ResourceController;

    protected string $resourceKey = 'subscription-user-subscriptions';
    protected string $viewPrefix = 'subscription::backend.user_subscription';

    public function __construct(protected Subscription $subscription)
    {
    }

    protected function getDataTable(...$params): \Juzaweb\CMS\Abstracts\DataTable
    {
        $dataTable = app(UserSubscriptionDatatable::class);
        $dataTable->mount($this->resourceKey, null);
        return $dataTable;
    }

    protected function validator(array $attributes, ...$params): \Illuminate\Contracts\Validation\Validator
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
        return UserSubscription::class;
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
