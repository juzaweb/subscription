<?php

namespace Juzaweb\Subscription\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Juzaweb\CMS\Repositories\BaseRepository;
use Juzaweb\Subscription\Models\Plan;

/**
 * Interface PlanRepository.
 *
 * @method Plan find($id, $columns = ['*']);
 */
interface PlanRepository extends BaseRepository
{
    public function adminPaginate(int $limit, ?int $page = null, array $columns = ['*']): LengthAwarePaginator;

    public function findByUUID(string $uuid, bool $fail = false): ?Plan;

    public function findByUUIDOrFail(string $uuid): Plan;
}
