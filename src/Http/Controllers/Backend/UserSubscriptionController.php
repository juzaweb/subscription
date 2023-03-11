<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Juzaweb\CMS\Traits\ResourceController;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Http\Datatables\UserSubscriptionDatatable;
use Juzaweb\Subscription\Models\UserSubscription;

class UserSubscriptionController extends BackendController
{
    use ResourceController;

    protected string $viewPrefix = 'subscription::backend.user_subscription';

    protected function getDataTable(...$params): UserSubscriptionDatatable
    {
        return new UserSubscriptionDatatable();
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
}
