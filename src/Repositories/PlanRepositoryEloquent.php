<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\Membership\Models\Package;

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
        return Package::class;
    }
}
