<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepository;
use Juzaweb\Subscription\Models\PaymentMethod;

/**
 * Interface PlanRepository.
 *
 * @method \Juzaweb\Subscription\Models\PaymentMethod find($id, $columns = ['*']);
 */
interface PaymentMethodRepository extends BaseRepository
{
    public function findByMethod(string $method, string $module, bool $fail = false): ?PaymentMethod;
}
