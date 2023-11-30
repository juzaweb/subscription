<?php

namespace Juzaweb\Subscription\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Models\User;
use Juzaweb\Network\Traits\Networkable;

/**
 * Juzaweb\Subscription\Models\PaymentHistory
 *
 * @property int $id
 * @property string $token Token of payment partner
 * @property string $method
 * @property string $module
 * @property string $type
 * @property float $amount
 * @property string $agreement_id Agreement of payment partner
 * @property Carbon|null $end_date
 * @property int $method_id
 * @property int $plan_id
 * @property int|null $user_subscription_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PaymentMethod $paymentMethod
 * @property-read Plan $plan
 * @property-read User|null $user
 * @method static Builder|PaymentHistory newModelQuery()
 * @method static Builder|PaymentHistory newQuery()
 * @method static Builder|PaymentHistory query()
 * @method static Builder|PaymentHistory whereAgreementId($value)
 * @method static Builder|PaymentHistory whereAmount($value)
 * @method static Builder|PaymentHistory whereCreatedAt($value)
 * @method static Builder|PaymentHistory whereEndDate($value)
 * @method static Builder|PaymentHistory whereId($value)
 * @method static Builder|PaymentHistory whereMethod($value)
 * @method static Builder|PaymentHistory whereMethodId($value)
 * @method static Builder|PaymentHistory whereModule($value)
 * @method static Builder|PaymentHistory wherePlanId($value)
 * @method static Builder|PaymentHistory whereToken($value)
 * @method static Builder|PaymentHistory whereType($value)
 * @method static Builder|PaymentHistory whereUpdatedAt($value)
 * @method static Builder|PaymentHistory whereUserId($value)
 * @method static Builder|PaymentHistory whereUserSubscriptionId($value)
 * @mixin Eloquent
 */
class PaymentHistory extends Model
{
    use Networkable;

    public const TYPE_WEBHOOK = 'webhook';
    public const TYPE_RETURN = 'return';

    protected $table = 'subscription_payment_histories';

    protected $fillable = [
        'token',
        'method',
        'module',
        'method_id',
        'plan_id',
        'user_subscription_id',
        'user_id',
        'agreement_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }
}
