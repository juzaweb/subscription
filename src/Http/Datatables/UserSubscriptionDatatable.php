<?php

namespace Juzaweb\Subscription\Http\Datatables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Juzaweb\CMS\Abstracts\DataTable;
use Juzaweb\Subscription\Models\UserSubscription;

class UserSubscriptionDatatable extends DataTable
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

    /**
     * Query data datatable
     *
     * @param array $data
     * @return Builder
     */
    public function query($data)
    {
        $query = UserSubscription::query();

        if ($keyword = Arr::get($data, 'keyword')) {
            $query->where(
                function (Builder $q) use ($keyword) {
                    // $q->where('title', JW_SQL_LIKE, '%'. $keyword .'%');
                }
            );
        }

        return $query;
    }

    public function bulkActions($action, $ids)
    {
        switch ($action) {
            case 'delete':
                SubscriptionUserSubscription::destroy($ids);
                break;
        }
    }
}
