<?php

namespace Juzaweb\Subscription\Repositories;

use Juzaweb\CMS\Repositories\BaseRepositoryEloquent;
use Juzaweb\CMS\Traits\ResourceRepositoryEloquent;
use Juzaweb\Subscription\Models\PaymentMethod;

class PaymentMethodRepositoryEloquent extends BaseRepositoryEloquent implements PaymentMethodRepository
{
    use ResourceRepositoryEloquent;

    public function findByMethod(string $method, string $module, $fail = false): ?PaymentMethod
    {
        $action = $fail ? 'firstOrFail' : 'first';

        return $this->model->query()->where(['method' => $method, 'module' => $module])->{$action}();
    }

    public function model(): string
    {
        return PaymentMethod::class;
    }
}
