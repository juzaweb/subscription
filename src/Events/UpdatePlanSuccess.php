<?php

namespace Juzaweb\Subscription\Events;

use Juzaweb\Subscription\Models\Plan;

class UpdatePlanSuccess
{
    public function __construct(public Plan $plan)
    {
        //
    }
}
