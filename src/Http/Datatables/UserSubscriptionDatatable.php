<?php

namespace Juzaweb\Subscription\Http\Datatables;

use Juzaweb\Backend\Http\Datatables\ResourceDatatable;

class UserSubscriptionDatatable extends ResourceDatatable
{
    /**
     * Columns datatable
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            'agreement_id' => [
                'label' => trans('subscription::content.agreement_id'),
            ],
            'amount' => [
                'label' => trans('subscription::content.amount'),
            ],
            'end_date' => [
                'label' => trans('subscription::content.end_date'),
            ],
            'method_id' => [
                'label' => trans('subscription::content.method'),
            ],
            'plan_id' => [
                'label' => trans('subscription::content.plan'),
            ],
            'start_date' => [
                'label' => trans('subscription::content.start_date'),
            ],
            'user_id' => [
                'label' => trans('subscription::content.user'),
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
