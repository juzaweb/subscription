<?php

namespace Juzaweb\Subscription\Contrasts;

use Juzaweb\Subscription\Models\Plan as ModelPlan;

interface PaymentMethod
{
    public function getName(): string;

    public function getLabel(): string;

    public function getConfigs(): array;

    /**
     * Create a plan
     *
     * @param ModelPlan $plan
     * @return string - identity plan id
     */
    public function createPlan(ModelPlan $plan): string;
}
