<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\Criterias\UseSearchCriteria;
use Juzaweb\CMS\Traits\Criterias\UseSortableCriteria;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\Plan;

class PlanRepositoryEloquent extends BaseRepositoryEloquent implements PlanRepository
{
    use ResourceRepositoryEloquent, UseSortableCriteria, UseSearchCriteria;

    protected array $sortableFields = ['id', 'price', 'is_free', 'enable_trial', 'status', 'created_at'];
    protected array $sortableDefaults = ['id' => 'DESC'];
    protected array $searchableFields = ['name'];

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
