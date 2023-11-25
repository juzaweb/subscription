<?php

namespace Juzaweb\Subscription\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Juzaweb\CMS\Contracts\GlobalDataContract;
use Juzaweb\CMS\Contracts\HookActionContract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentMethodManager as PaymentMethodManagerContrast;
use Juzaweb\Subscription\Models\PaymentMethod as PaymentMethodModel;
use Juzaweb\Subscription\Repositories\PaymentMethodRepository;
use Throwable;

class PaymentMethodManager implements PaymentMethodManagerContrast
{
    public function __construct(
        protected HookActionContract $hookAction,
        protected GlobalDataContract $globalData,
        protected PaymentMethodRepository $paymentMethodRepository
    ) {
    }

    public function all(): Collection
    {
        return collect($this->globalData->get("subscription_methods"));
    }

    public function get(string $method): ?Collection
    {
        return $this->all()->where('key', $method)->first();
    }

    public function register(string $method, string $concrete, array $configs = []): void
    {
        $args = [
            'key' => $method,
            'label' => $configs['label'] ?? Str::ucfirst($method),
            'class' => $concrete,
        ];

        $this->globalData->set("subscription_methods.{$args['key']}", new Collection($args));
    }

    public function find(PaymentMethodModel $method): PaymentMethod
    {
        $helper = $this->get($method->method);

        throw_unless($helper, new \Exception('Payment Method does not exist.'));

        return app($helper->get('class'), ['paymentMethod' => $method]);
    }
}
