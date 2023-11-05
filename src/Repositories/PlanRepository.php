<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepository;
use Juzaweb\Subscription\Models\Plan;

/**
 * Interface PlanRepository.
 *
 * @method \Juzaweb\Subscription\Models\Plan find($id, $columns = ['*']);
 */
interface PlanRepository extends BaseRepository
{
    public function findByUUID(string $uuid, bool $fail = false): ?Plan;

    public function findByUUIDOrFail(string $uuid): Plan;
}
