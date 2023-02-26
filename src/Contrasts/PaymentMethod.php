<?php

namespace Juzaweb\Subscription\Contrasts;

use Juzaweb\Subscription\Models\Plan as ModelPlan;

interface PaymentMethod
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return array
     */
    public function getConfigs(): array;

    public function isRedirect(): bool;

    public function getRedirectUrl(): string;

    /**
     * Create a plan
     *
     * @return string - identity plan id
     */
    public function createPlan(): string;
}
