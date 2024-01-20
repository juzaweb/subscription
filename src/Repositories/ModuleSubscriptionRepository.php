<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepository;
use Juzaweb\Subscription\Models\ModuleSubscription;

/**
 * Interface ModuleSubscriptionRepository.
 *
 * @method ModuleSubscription find($id, $columns = ['*']);
 */
interface ModuleSubscriptionRepository extends BaseRepository
{
    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator;
}
