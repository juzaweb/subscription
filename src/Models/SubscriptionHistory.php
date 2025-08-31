<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;
use Juzaweb\Modules\Subscription\Enums\SubscriptionHistoryStatus;

class SubscriptionHistory extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'subscription_histories';

    protected $fillable = [
        'method',
        'module',
        'amount',
        'agreement_id',
        'end_date',
        'method_id',
        'plan_id',
        'user_id',
        'subscription_id',
        'status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'end_date' => 'datetime',
        'amount' => 'decimal:2',
        'status' => SubscriptionHistoryStatus::class,
    ];
}
