<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Models\User;
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
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property-read \Juzaweb\Subscription\Models\PaymentMethod $paymentMethod
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereStartDate($value)
 */
class UserSubscription extends Model
{
    use UseUUIDColumn;

    protected $table = 'subscription_user_subscriptions';

    protected $fillable = [
        'module',
        'agreement_id',
        'amount',
        'method_id',
        'user_id',
        'plan_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }
}
