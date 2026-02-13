<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Subscription\Enums\SubscriptionStatus;

class Subscription extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'subscriptions';

    protected $fillable = [
        'driver',
        'module',
        'amount',
        'agreement_id',
        'start_date',
        'end_date',
        'method_id',
        'plan_id',
        'billable_id',
        'billable_type',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => SubscriptionStatus::class,
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(SubscriptionMethod::class, 'method_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeWhereIsValid(Builder $builder): Builder
    {
        return $builder->where('status', SubscriptionStatus::ACTIVE)
            ->where('end_date', '>', now());
    }

    public function isValid(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE
            && $this->end_date > now();
    }
}
