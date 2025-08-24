<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class PlanSubscriptionMethod extends Model
{
    use HasAPI;

    protected $table = 'plan_subscription_methods';

    protected $fillable = [
        'payment_plan_id',
        'plan_id',
        'method',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
