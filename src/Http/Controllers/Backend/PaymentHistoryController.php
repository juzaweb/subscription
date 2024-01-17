<?php

namespace Juzaweb\Subscription\Http\Controllers\Backend;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Collection;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\CMS\Facades\HookAction;
use Juzaweb\CMS\Traits\ResourceController;
use Illuminate\Support\Facades\Validator;
use Juzaweb\CMS\Http\Controllers\BackendController;
use Juzaweb\Subscription\Contrasts\Subscription;
use Juzaweb\Subscription\Http\Datatables\PaymentHistoryDatatable;
use Juzaweb\Subscription\Models\PaymentHistory;

class PaymentHistoryController extends BackendController
{
    use ResourceController;

    protected string $resourceKey = 'subscription-payment-histories';
    protected string $viewPrefix = 'subscription::backend.payment_history';

    public function __construct(protected Subscription $subscription)
    {
    }

    protected function getDataTable(...$params): DataTable
    {
        $datatable = app()->make(PaymentHistoryDatatable::class);
        $datatable->mount($this->resourceKey, null);
        return $datatable;
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

    protected function validator(array $attributes, ...$params): ValidatorContract
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
