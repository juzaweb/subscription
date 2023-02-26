<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\Plan;

class PlanRepositoryEloquent extends BaseRepositoryEloquent implements PlanRepository
{
    use ResourceRepositoryEloquent;

    public function model(): string
    {
        return Plan::class;
    }
}
