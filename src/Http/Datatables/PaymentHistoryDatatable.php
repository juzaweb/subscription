<?php

namespace Juzaweb\Subscription\Http\Datatables;

use Juzaweb\Backend\Http\Datatables\ResourceDatatable;
use Juzaweb\CMS\Abstracts\DataTable;

class PaymentHistoryDatatable extends ResourceDatatable
{
    /**
     * Columns datatable
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            // 'title' => [
            //     'label' => trans('cms::app.title'),
            //     'formatter' => [$this, 'rowActionsFormatter'],
            // ],
            'agreement_id' => [
                'label' => trans('subscription::content.agreement_id'),
            ],
            'amount' => [
                'label' => trans('subscription::content.amount'),
            ],
            'end_date' => [
                'label' => trans('subscription::content.end_date'),
            ],
            'method' => [
                'label' => trans('subscription::content.method'),
            ],
            'method_id' => [
                'label' => trans('subscription::content.method_id'),
            ],
            'module' => [
                'label' => trans('subscription::content.module'),
            ],
            'plan_id' => [
                'label' => trans('subscription::content.plan_id'),
            ],
            'token' => [
                'label' => trans('subscription::content.token'),
            ],
            'type' => [
                'label' => trans('subscription::content.type'),
            ],
            'user_id' => [
                'label' => trans('subscription::content.user_id'),
            ],
            'user_subscription_id' => [
                'label' => trans('subscription::content.user_subscription_id'),
            ],
            'created_at' => [
                'label' => trans('cms::app.created_at'),
                'width' => '15%',
                'align' => 'center',
                'formatter' => function ($value, $row, $index) {
                    return jw_date_format($row->created_at);
                }
            ]
        ];
    }
}
