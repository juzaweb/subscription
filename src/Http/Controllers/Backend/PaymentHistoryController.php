<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Support\Collection;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Http\Datatables\PaymentHistoryDatatable;
use Juzaweb\Subscription\Models\PaymentHistory;

class PaymentHistoryController extends BackendController
{
    use ResourceController;

    protected string $resourceKey = 'subscription-payment-histories';
    protected string $viewPrefix = 'subscription::backend.payment_history';

    protected function getDataTable(...$params): PaymentHistoryDatatable
    {
        $datatable = new PaymentHistoryDatatable();
        $datatable->mount($this->resourceKey, null);
        return $datatable;
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
        return PaymentHistory::class;
    }

    protected function getTitle(...$params): string
    {
        return trans('subscription::content.payment_histories');
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
