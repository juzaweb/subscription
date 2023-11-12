<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\Criterias\UseSearchCriteria;
use Juzaweb\CMS\Traits\Criterias\UseSortableCriteria;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\Plan;

class PlanRepositoryEloquent extends BaseRepositoryEloquent implements PlanRepository
{
    use ResourceRepositoryEloquent, UseSortableCriteria, UseSearchCriteria;

    protected array $filterableFields = ['module', 'status', 'is_free'];
    protected array $sortableFields = ['id', 'price', 'is_free', 'enable_trial', 'status', 'created_at'];
    protected array $sortableDefaults = ['id' => 'DESC'];
    protected array $searchableFields = ['name'];

    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->newQuery()
            ->where(['module' => $this->app['router']->current()?->parameter('module')])
            ->paginate($limit, $columns, 'page', $page);
        $results->appends($this->app['request']->query());

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function findByUUID(string $uuid, bool $fail = false): ?Plan
    {
        $action = $fail ? 'firstOrFail' : 'first';

        return $this->model->query()->where(['uuid' => $uuid])->{$action}();
    }

    public function findByUUIDOrFail(string $uuid): Plan
    {
        return $this->findByUUID($uuid, true);
    }

    public function model(): string
    {
        return Plan::class;
    }
}
