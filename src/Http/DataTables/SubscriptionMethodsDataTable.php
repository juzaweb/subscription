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
use Illuminate\Database\Eloquent\Model;
use Juzaweb\Core\DataTables\Action;
use Juzaweb\Core\DataTables\BulkAction;
use Juzaweb\Core\DataTables\Column;
use Juzaweb\Core\DataTables\DataTable;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;
use Yajra\DataTables\EloquentDataTable;

class SubscriptionMethodsDataTable extends DataTable
{
    protected string $actionUrl = 'subscription-methods/bulk';

    public function query(SubscriptionMethod $model): Builder
    {
        return $model->newQuery()->withTranslation()->filter(request()->all());
    }

    public function getColumns(): array
    {
        return [
            Column::checkbox(),
            Column::id(),
            Column::editLink('name', admin_url('subscription-methods/{id}/edit'), __('Name')),
            Column::make('sandbox', __('Sandbox'))->center()->width('100px'),
            Column::make('active', __('Active'))
                ->center()
                ->width('100px'),
            Column::createdAt(),
            Column::actions(),
        ];
    }

    public function renderColumns(EloquentDataTable $builder): EloquentDataTable
    {
        return $builder
            ->editColumn(
                'active',
                fn (SubscriptionMethod $model) => $model->active ? __('Yes') : __('No'))
            ->editColumn(
                'sandbox',
                fn (SubscriptionMethod $model) => $model->sandbox ? __('Yes') : __('No')
            );
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::delete()->can('subscription-methods.delete'),
        ];
    }

    public function actions(Model $model): array
    {
        return [
            Action::edit(admin_url("subscription-methods/{$model->id}/edit"))
                ->can('subscription-methods.edit'),
            Action::delete()
                ->can('subscription-methods.delete'),
        ];
    }
}
