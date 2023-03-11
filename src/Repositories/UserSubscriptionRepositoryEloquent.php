<?php

namespace Juzaweb\Subscription\Repositories;

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
}
