<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\PlanPaymentMethod
 *
 * @property int $id
 * @property string $payment_plan_id Plan id of payment service
 * @property string $method
 * @property int $plan_id
 * @property int $method_id
 * @property-read PaymentMethod $paymentMethod
 * @property-read Plan $plan
 * @method static Builder|PlanPaymentMethod newModelQuery()
 * @method static Builder|PlanPaymentMethod newQuery()
 * @method static Builder|PlanPaymentMethod query()
 * @method static Builder|PlanPaymentMethod whereId($value)
 * @method static Builder|PlanPaymentMethod whereMethod($value)
 * @method static Builder|PlanPaymentMethod whereMethodId($value)
 * @method static Builder|PlanPaymentMethod wherePaymentPlanId($value)
 * @method static Builder|PlanPaymentMethod wherePlanId($value)
 * @mixin \Eloquent
 */
class PlanPaymentMethod extends Model
{
    public $timestamps = false;

    protected $table = 'subscription_plan_payment_methods';

    protected $fillable = [
        'method',
        'method_id',
        'payment_plan_id',
        'plan_id',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }
}
