<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\PaymentHistory
 *
 * @property int $id
 * @property string $token Token of payment partner
 * @property string $method
 * @property int $method_id
 * @property int $plan_id
 * @property int|null $user_subscription_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUserSubscriptionId($value)
 * @mixin \Eloquent
 * @property string $module
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereModule($value)
 * @property string $agreement_id Agreement of payment partner
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereAgreementId($value)
 */
class PaymentHistory extends Model
{
    protected $table = 'subscription_payment_histories';

    protected $fillable = [
        'token',
        'method',
        'method_id',
        'plan_id',
        'user_subscription_id',
        'user_id',
        'agreement_id',
    ];

    protected $casts = [
        'end_date' => 'datetime',
    ];
}
