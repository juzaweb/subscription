<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\Subscription\Models\Plan;

class PlanRepositoryEloquent extends BaseRepositoryEloquent implements PlanRepository
{
    public function adminPaginate(int $limit, int $page = null, $columns = ['*']): mixed
    {
        $this->applyCriteria();
        $this->applyScope();
        $results = $this->model->paginate($limit, $columns, 'page', $page);
        $results->appends(app('request')->query());
        $this->resetModel();

        return $this->parserResult($results);
    }

    public function model(): string
    {
        return Plan::class;
    }
}
