<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\QueryCache\QueryCacheable;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\CMS\Traits\UseUUIDColumn;
use Juzaweb\Network\Traits\Networkable;

/**
 * Juzaweb\Subscription\Models\Plan
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property bool $is_free
 * @property string $status
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|PaymentMethod[] $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property-read Collection|PlanPaymentMethod[] $planPaymentMethods
 * @property-read int|null $plan_payment_methods_count
 * @property Collection|PlanFeature[] $features
 * @method static Builder|Plan newModelQuery()
 * @method static Builder|Plan newQuery()
 * @method static Builder|Plan query()
 * @method static Builder|Plan whereCreatedAt($value)
 * @method static Builder|Plan whereDescription($value)
 * @method static Builder|Plan whereFilter($params = [])
 * @method static Builder|Plan whereId($value)
 * @method static Builder|Plan whereIsActive()
 * @method static Builder|Plan whereIsFree($value)
 * @method static Builder|Plan whereModule($value)
 * @method static Builder|Plan whereName($value)
 * @method static Builder|Plan wherePrice($value)
 * @method static Builder|Plan whereStatus($value)
 * @method static Builder|Plan whereUpdatedAt($value)
 * @method static Builder|Plan whereUuid($value)
 * @property int|null $site_id
 * @property-read int|null $features_count
 * @method static Builder|Plan whereSiteId($value)
 * @mixin \Eloquent
 */
class Plan extends Model
{
    use ResourceModel, UseUUIDColumn, QueryCacheable, Networkable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_free',
        'status',
        'module',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
    ];

    public static function getAllstatus(): array
    {
        return [
            static::STATUS_ACTIVE => trans('cms::app.active'),
            static::STATUS_INACTIVE => trans('cms::app.inactive'),
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

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id', 'id');
    }

    public function scopeWhereIsActive(Builder $builder): Builder
    {
        return $builder->where('status', '=', self::STATUS_ACTIVE);
    }
}
