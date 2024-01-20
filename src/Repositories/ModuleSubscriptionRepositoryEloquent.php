<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Route;
use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\Criterias\UseSearchCriteria;
use Juzaweb\CMS\Traits\Criterias\UseSortableCriteria;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\ModuleSubscription;

class ModuleSubscriptionRepositoryEloquent extends BaseRepositoryEloquent implements ModuleSubscriptionRepository
{
    use ResourceRepositoryEloquent, UseSearchCriteria, UseSortableCriteria;

    protected array $searchableFields = ['agreement_id'];
    protected array $sortableDefaults = ['id' => 'DESC'];

    public function model(): string
    {
        return ModuleSubscription::class;
    }

    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->newQuery()->with(['plan', 'user', 'paymentMethod'])
            ->where(['module_type' => Route::getCurrentRoute()?->parameter('module')])
            ->paginate($limit, $columns, 'page', $page);
        $results->appends(app('request')->query());

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }
}
