<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\Criterias\UseSearchCriteria;
use Juzaweb\CMS\Traits\Criterias\UseSortableCriteria;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\UserSubscription;

class UserSubscriptionRepositoryEloquent extends BaseRepositoryEloquent implements UserSubscriptionRepository
{
    use ResourceRepositoryEloquent, UseSearchCriteria, UseSortableCriteria;

    protected array $searchableFields = ['agreement_id'];
    protected array $sortableDefaults = ['id' => 'DESC'];

    public function model(): string
    {
        return UserSubscription::class;
    }

    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->with(['plan', 'user', 'paymentMethod'])
            ->paginate($limit, $columns, 'page', $page);
        $results->appends(app('request')->query());

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }
}
