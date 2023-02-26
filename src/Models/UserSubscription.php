<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\UserSubscription
 *
 * @property int $id
 * @property string $token
 * @property string $role
 * @property string $method
 * @property string $agreement_id
 * @property string $payer_id
 * @property string $payer_email
 * @property float $amount
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
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription wherePayerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription wherePayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUserId($value)
 * @mixin \Eloquent
 */
class UserSubscription extends Model
{
    protected $table = 'membership_user_subscriptions';

    protected $fillable = [
        'agreement_id',
        'amount',
        'method',
        'payer_email',
        'payer_id',
        'role',
        'token',
        'user_id',
    ];
}
