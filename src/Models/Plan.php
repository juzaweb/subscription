<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\CMS\Traits\UseUUIDColumn;

/**
 * Juzaweb\Subscription\Models\Plan
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property bool $is_free
 * @property bool $enable_trial
 * @property string $status
 * @property string $module
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|\Juzaweb\Subscription\Models\PlanPaymentMethod[] $planPaymentMethods
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereEnableTrial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereFilter($params = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereIsFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string $uuid
 * @property int $free_trial_days
 * @property-read Collection|\Juzaweb\Subscription\Models\PaymentMethod[] $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property-read int|null $plan_payment_methods_count
 * @method static Builder|Plan whereFreeTrialDays($value)
 * @method static Builder|Plan whereIsActive()
 * @method static Builder|Plan whereUuid($value)
 */
class Plan extends Model
{
    const STATUS_ACTIVE = 'active';

    use ResourceModel, UseUUIDColumn;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_free',
        'enable_trial',
        'status',
        'module',
        'free_trial_days',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'enable_trial' => 'boolean',
    ];

    public static function getAllstatus(): array
    {
        return [
            'active' => trans('cms::app.active'),
            'draft' => trans('cms::app.draft'),
        ];
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(
            PaymentMethod::class,
            PlanPaymentMethod::class,
            'plan_id',
            'method_id',
            'id',
            'id'
        );
    }

    public function planPaymentMethods(): HasMany
    {
        return $this->hasMany(PlanPaymentMethod::class, 'plan_id', 'id');
    }

    public function scopeWhereIsActive(Builder $builder): Builder
    {
        return $builder->where('status', '=', self::STATUS_ACTIVE);
    }
}
