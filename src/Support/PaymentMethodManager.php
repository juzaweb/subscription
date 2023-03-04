<?php

namespace Juzaweb\Subscription\Support;

use Illuminate\Support\Collection;
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

    /**
     * @throws Throwable
     */
    public function register(string|PaymentMethod $method): void
    {
        if (!$method instanceof PaymentMethod) {
            $method = app($method);
        }

        $args = [
            'key' => $method->getName(),
            'label' => $method->getLabel(),
            'class' => get_class($method),
            'configs' => $method->getConfigs(),
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
