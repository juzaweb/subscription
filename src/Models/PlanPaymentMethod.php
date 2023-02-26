<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\PlanPaymentMethod
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod query()
 * @mixin \Eloquent
 */
class PlanPaymentMethod extends Model
{
    protected $table = 'subscription_plan_payment_methods';

    protected $fillable = [
        'method',
        'method_id',
        'payment_plan_id',
        'plan_id',
    ];
}
