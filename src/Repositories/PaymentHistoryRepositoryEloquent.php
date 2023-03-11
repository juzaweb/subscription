<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\UserSubscription;

class PaymentHistoryRepositoryEloquent extends BaseRepositoryEloquent implements UserSubscriptionRepository
{
    use ResourceRepositoryEloquent;

    public function model(): string
    {
        return UserSubscription::class;
    }
}
