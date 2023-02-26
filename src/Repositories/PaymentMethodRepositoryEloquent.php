<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\PaymentMethod;

class PaymentMethodRepositoryEloquent extends BaseRepositoryEloquent implements PlanRepository
{
    use ResourceRepositoryEloquent;

    public function model(): string
    {
        return PaymentMethod::class;
    }
}
