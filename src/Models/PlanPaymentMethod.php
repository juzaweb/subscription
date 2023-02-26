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
 * @property int $id
 * @property string $payment_plan_id Plan id of payment service
 * @property string $method
 * @property int $plan_id
 * @property int $method_id
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod wherePaymentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPaymentMethod wherePlanId($value)
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
