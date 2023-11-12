<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\PaymentMethod;

class PaymentMethodRepositoryEloquent extends BaseRepositoryEloquent implements PaymentMethodRepository
{
    use ResourceRepositoryEloquent;

    public function findByMethod(string $method, string $module, $fail = false): ?PaymentMethod
    {
        $action = $fail ? 'firstOrFail' : 'first';

        return $this->model->query()->where(['method' => $method, 'module' => $module])->{$action}();
    }

    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->with([])
            ->where(['module' => $this->app['router']->current()?->parameter('module')])
            ->paginate($limit, $columns, 'page', $page);
        $results->appends(app('request')->query());

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    public function model(): string
    {
        return PaymentMethod::class;
    }
}
