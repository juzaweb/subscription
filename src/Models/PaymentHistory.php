<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\PaymentHistory
 *
 * @property int $id
 * @property string $method
 * @property int $user_subscription_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentHistory whereUserSubscriptionId($value)
 * @mixin \Eloquent
 */
class PaymentHistory extends Model
{
    protected $table = 'subscription_payment_histories';
    protected $fillable = [
        'method',
        'user_id',
        'user_subscription_id',
    ];
}
