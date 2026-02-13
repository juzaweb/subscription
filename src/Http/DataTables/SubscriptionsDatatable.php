<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    larabizcom/larabiz
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 */

namespace Juzaweb\Modules\Subscription\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Juzaweb\Modules\Subscription\Models\Subscription;
use Yajra\DataTables\EloquentDataTable;

class SubscriptionsDatatable extends DataTable
{
    protected string $actionUrl = 'subscriptions/bulk';

    public function query(Subscription $model): Builder
    {
        return $model->newQuery()->with(['billable', 'plan', 'method']);
    }

    public function getColumns(): array
    {
        return [
            Column::make('agreement_id')->title(__('Bill ID')),
			Column::make('start_date'),
            Column::make('end_date'),
            Column::make('method_id'),
            Column::make('plan_id'),
            Column::make('amount'),
            Column::computed('billable'),
			Column::make('status'),
			Column::createdAt(),
		];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        return parent::renderColumns($builder)
            ->editColumn(
                'start_date',
                fn (Subscription $model) => $model->start_date ? $model->start_date->format('Y-m-d') : ''
            )
            ->editColumn(
                'end_date',
                fn (Subscription $model) => $model->end_date ? $model->end_date->format('Y-m-d') : ''
            )
            ->editColumn(
                'plan_id',
                function (Subscription $model) {
                    return $model->plan?->name;
                }
            )
            ->editColumn(
                'method_id',
                function (Subscription $model) {
                    return $model->method?->name;
                }
            )
            ->editColumn(
                'billable',
                function (Subscription $model) {
                    return $model->billable ? $model->billable->name : '';
                }
            );
    }
}
