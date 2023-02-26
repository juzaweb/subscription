<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Collection;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;

/**
 * Juzaweb\Subscription\Models\Plan
 *
 * @property-read Collection|PlanPaymentMethod[] $planPaymentMethods
 * @property-read int|null $plan_payment_methods_count
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereFilter($params = [])
 * @mixin \Eloquent
 */
class Plan extends Model
{
    use ResourceModel;

    protected $table = 'membership_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_free',
        'enable_trial',
        'status',
        'module',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'enable_trial' => 'boolean',
    ];

    public function planPaymentMethods(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlanPaymentMethod::class, 'plan_id', 'id');
    }
}
