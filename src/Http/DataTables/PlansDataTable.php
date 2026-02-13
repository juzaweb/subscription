<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Juzaweb\Modules\Core\DataTables\BulkAction;
use Juzaweb\Modules\Core\DataTables\Column;
use Juzaweb\Modules\Core\DataTables\DataTable;
use Juzaweb\Modules\Subscription\Models\Plan;
use Yajra\DataTables\EloquentDataTable;

class PlansDataTable extends DataTable
{
    protected string $actionUrl = 'plans/bulk';

    public function query(Plan $model): Builder
    {
        return $model->newQuery()
            ->where('module', $this->getAttribute('module'))
            ->filter(request()->all());
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::editLink(
                'name',
                admin_url('subscription/{module}/plans/{id}/edit'),
                __('Name')
            ),
            Column::make('active', __('Active'))
                ->center()
                ->width('100px'),
            Column::createdAt(),
        ];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        return $builder
            ->editColumn(
                'active',
                fn(Plan $model) => $model->active ? __('Yes') : __('No'));
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make(__('Activate'))->can('plans.edit'),
            BulkAction::make(__('Deactivate'))->can('plans.edit'),
        ];
    }
}
