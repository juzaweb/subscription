<?php

namespace Juzaweb\Subscription\Contrasts;

interface PaymentMethodManager
{
    /**
     * Registers subscription Payment Method
     * This function registers a new subscription method,
     * setting a default key and label, as well as any additional configs passed as an argument.
     *
     * @param string $method Method name
     * @param array $args Optional. Method configs
     *
     * @return void
     */
    public function register(string $method, array $args = []): void;
}
