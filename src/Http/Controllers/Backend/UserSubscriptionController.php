<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Support\Collection;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Http\Datatables\PlanDatatable;
use Juzaweb\Subscription\Http\Datatables\UserSubscriptionDatatable;
use Juzaweb\Subscription\Models\UserSubscription;

class UserSubscriptionController extends BackendController
{
    use ResourceController;

    protected string $resourceKey = 'subscription-user-subscriptions';
    protected string $viewPrefix = 'subscription::backend.user_subscription';

    protected function getDataTable(...$params)
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
