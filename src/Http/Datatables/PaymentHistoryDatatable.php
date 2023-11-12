<?php

namespace Juzaweb\Subscription\Http\Datatables;

use Juzaweb\Backend\Http\Datatables\PostType\ResourceDatatable;
use Juzaweb\Subscription\Models\PaymentHistory;

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
            'agreement_id' => [
                'label' => trans('subscription::content.agreement_id'),
            ],
            'amount' => [
                'label' => trans('subscription::content.amount'),
                'formatter' => fn($value, $row, $index)
                    => $row->type == PaymentHistory::TYPE_RETURN ? "-" : "$".$value,
            ],
            'method' => [
                'label' => trans('subscription::content.method'),
                'formatter' => fn($value, $row, $index) => $row->paymentMethod?->name,
            ],
            'plan_id' => [
                'label' => trans('subscription::content.plan'),
                'formatter' => function ($value, $row, $index) {
                    return $row->plan?->name;
                }
            ],
            'user_id' => [
                'label' => trans('subscription::content.user'),
                'formatter' => function ($value, $row, $index) {
                    return $row->user?->name;
                }
            ],
            'end_date' => [
                'label' => trans('subscription::content.end_date'),
                'formatter' => function ($value, $row, $index) {
                    return $row->end_date ? jw_date_format($row->end_date, 'Y-m-d') : '_';
                }
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
