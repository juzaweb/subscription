<?php

namespace Juzaweb\Subscription\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Models\User;
use Juzaweb\CMS\Traits\QueryCache\QueryCacheable;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\CMS\Traits\UseUUIDColumn;
use Juzaweb\Network\Traits\Networkable;

/**
 * Juzaweb\Subscription\Models\ModuleSubscription
 *
 * @property-read PaymentMethod|null $method
 * @property-read Plan|null $plan
 * @property-read User|null $user
 * @method static Builder|ModuleSubscription newModelQuery()
 * @method static Builder|ModuleSubscription newQuery()
 * @method static Builder|ModuleSubscription query()
 * @method static Builder|ModuleSubscription whereFilter(array $params = [])
 * @mixin Eloquent
 */
class ModuleSubscription extends Model
{
    use ResourceModel, UseUUIDColumn, Networkable, QueryCacheable;

    protected $table = 'subscription_module_subscriptions';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPEND = 'suspend';
    public const STATUS_CANCEL = 'cancel';

    protected $fillable = [
        'register_by',
        'module_id',
        'module_type',
        'agreement_id',
        'amount',
        'start_date',
        'end_date',
        'method_id',
        'plan_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'register_by', 'id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'module_id', 'id');
    }

    public function scopeInEffect(Builder $query): Builder
    {
        return $query->isActive()->where('end_date', '>=', now());
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeIsFree(Builder $query): Builder
    {
        return $query->where('is_free', true);
    }

    public function scopeIsCancel(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCEL);
    }

    public function scopeIsSuspend(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUSPEND);
    }

    public function expired(): bool
    {
        return empty($this->end_date) || $this->end_date->lt(now());
    }

    public function unexpired(): bool
    {
        return !$this->expired();
    }

    public function activeSubscription(): void
    {
        $expirationDate = now()->addMonth()->format('Y-m-d 23:59:59');
        $this->setAttribute('start_date', now());
        $this->setAttribute('end_date', $expirationDate);
        $this->setAttribute('status', self::STATUS_ACTIVE);
        $this->save();
    }

    public function cancelSubscription(string $status = self::STATUS_CANCEL): void
    {
        $this->setAttribute('status', $status);
        $this->setAttribute('end_date', null);
        $this->save();
    }
}
