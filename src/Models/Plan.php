<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class Plan extends Model
{
    use HasAPI;

    protected $table = 'plans';

    protected $fillable = [
        'price',
        'is_free',
        'status',
        'module',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
    ];

    public function subscriptionMethods(): HasMany
    {
        return $this->hasMany(PlanSubscriptionMethod::class, 'plan_id');
    }
}
