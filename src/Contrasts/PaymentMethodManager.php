<?php

namespace Juzaweb\Subscription\Contrasts;

use Illuminate\Support\Collection;
use Juzaweb\Subscription\Models\PaymentMethod as PaymentMethodModel;

interface PaymentMethodManager
{
    /**
     * Registers subscription Payment Method
     * This function registers a new subscription method,
     * setting a default key and label, as well as any additional configs passed as an argument.
     *
     * @param  string  $method  Method name
     * @param  class-string  $concrete
     * @param  array  $configs
     * @return void
     */
    public function register(string $method, string $concrete, array $configs = []): void;

    /**
     * Retrieves all the subscription methods.
     *
     * @return Collection The collection of subscription methods.
     */
    public function all(): Collection;

    /**
     * Retrieves a specific item from the collection based on the provided method.
     *
     * @param string $method The key used to retrieve the item.
     * @return ?Collection The retrieved item, or null if it doesn't exist.
     */
    public function get(string $method): ?Collection;

    /**
     * Find a payment method.
     *
     * @param PaymentMethodModel $method The payment method to find.
     * @throws \Exception Payment Method does not exist.
     * @return PaymentMethod The found payment method.
     */
    public function find(PaymentMethodModel $method): PaymentMethod;
}
