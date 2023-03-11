<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepository;

/**
 * Interface UserSubscriptionRepository.
 *
 * @method \Juzaweb\Subscription\Models\UserSubscription find($id, $columns = ['*']);
 */
interface PaymentHistoryRepository extends BaseRepository
{
    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator;
}
