<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;

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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
