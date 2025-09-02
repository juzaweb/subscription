<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;
use Juzaweb\Modules\Subscription\Enums\SubscriptionStatus;

class Subscription extends Model
{
    use HasAPI;

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
        'user_id',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => SubscriptionStatus::class,
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
}
