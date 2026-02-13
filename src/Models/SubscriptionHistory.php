<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Subscription\Enums\SubscriptionHistoryStatus;

class SubscriptionHistory extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'subscription_histories';

    protected $fillable = [
        'driver',
        'module',
        'amount',
        'agreement_id',
        'end_date',
        'method_id',
        'plan_id',
        'subscription_id',
        'status',
        'data',
        'billable_id',
        'billable_type',
    ];

    protected $casts = [
        'data' => 'array',
        'end_date' => 'datetime',
        'amount' => 'decimal:2',
        'status' => SubscriptionHistoryStatus::class,
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function method()
    {
        return $this->belongsTo(SubscriptionMethod::class, 'method_id');
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
