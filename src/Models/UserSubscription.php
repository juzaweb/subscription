<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\UseUUIDColumn;

/**
 * Juzaweb\Subscription\Models\UserSubscription
 *
 * @property int $id
 * @property string $uuid
 * @property string $agreement_id Agreement of payment partner
 * @property float $amount
 * @property int $method_id
 * @property int $plan_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereAgreementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUuid($value)
 * @mixin \Eloquent
 * @property string $module
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereModule($value)
 */
class UserSubscription extends Model
{
    use UseUUIDColumn;

    protected $table = 'subscription_user_subscriptions';

    protected $fillable = [
        'agreement_id',
        'amount',
        'method_id',
        'user_id',
    ];
}
