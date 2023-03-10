<?php

namespace Juzaweb\Subscription\Http\Datatables;

use Juzaweb\Backend\Http\Datatables\ResourceDatatable;

class PlanDatatable extends ResourceDatatable
{
    public function columns(): array
    {
        return [
            /*'action' => [
                'label' => trans('cms::app.actions'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => fn($value, $row, $index) =>
                    view(
                        'subscription::plan.components.datatable.plan_action',
                        compact('value', 'row', 'index')
                    ),
            ],*/
            'name' => [
                'label' => trans('cms::app.name'),
                'formatter' => [$this, 'rowActionsFormatter'],
            ],
            'price' => [
                'label' => trans('subscription::content.price'),
                'width' => '15%',
                'align' => 'center',
                'formatter' => fn($value, $row, $index) => '$'. number_format($value),
            ],
            'is_free' => [
                'label' => trans('subscription::content.is_free'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => fn($value, $row, $index) => $value == 1
                    ? '<span class="text-success"><i class="fa fa-check"></i></span>'
                    : '_',
            ],
            'enable_trial' => [
                'label' => trans('subscription::content.enable_trial'),
                'width' => '10%',
                'align' => 'center',
                'formatter' => fn($value, $row, $index) => $value == 1
                    ? '<span class="text-success"><i class="fa fa-check"></i></span>'
                    : '_',
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
